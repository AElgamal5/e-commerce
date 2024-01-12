<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Color;
use App\Models\Language;
use App\Models\ColorTranslation;

use App\Filters\v1\ColorFilter;

use App\Http\Resources\v1\ColorCollection;
use App\Http\Resources\v1\ColorResource;

use App\Http\Requests\v1\Color\IndexColorRequest;
use App\Http\Requests\v1\Color\StoreColorRequest;
use App\Http\Requests\v1\Color\ShowColorRequest;
use App\Http\Requests\v1\Color\UpdateColorRequest;
use App\Http\Requests\v1\Color\DestroyColorRequest;

use App\Services\v1\ColorService;

class ColorController extends Controller
{
    public function index(IndexColorRequest $request)
    {
        $filter = new ColorFilter();
        $filterItems = $filter->transform($request);

        $colors = Color::where('deleted_by', '=', null)->where($filterItems);

        if ($request->query('createdByUser') == 'true') {
            $colors = $colors->with('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $colors = $colors->with('updatedByUser');
        }

        if ($request->query('translations') == '*') {
            $colors = $colors->with('translations.language');
        } elseif ($request->has('translations')) {

            $langId = $request->query('translations');

            $colors = $colors->with([
                'translations' => function ($query) use ($langId) {
                    $query->where('language_id', $langId)->where('deleted_by', null);
                }
            ]);
        }

        return new ColorCollection($colors->paginate()->appends($request->query()));
    }

    public function store(StoreColorRequest $request, ColorService $colorService)
    {
        $uniquenessErrors = $colorService->uniquenessChecks($request);
        if ($uniquenessErrors) {
            return $uniquenessErrors;
        }

        $input = $request->all();

        $color = Color::create([
            'code' => $input['code'],
            'created_by' => $input['createdBy'],
        ]);

        foreach ($input['translations'] as $trans) {
            $color->translations()->create([
                'language_id' => $trans['languageId'],
                'name' => $trans['name'],
                'created_by' => $input['createdBy'],
            ]);
        }

        return new ColorResource($color);
    }

    public function show(ShowColorRequest $request, Color $color, ColorService $colorService)
    {

        $existenceErrors = $colorService->existenceCheck($color);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        if ($request->query('createdByUser') == 'true') {
            $color = $color->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $color = $color->loadMissing('updatedByUser');
        }

        if ($request->query('translations') == '*') {
            $color = $color->loadMissing('translations.language');
        } elseif ($request->has('translations')) {

            $langId = $request->query('translations');

            $color = $color->loadMissing([
                'translations' => function ($query) use ($langId) {
                    $query->where('language_id', $langId)->where('deleted_by', null);
                }
            ]);
        }

        return new ColorResource($color);
    }

    public function update(UpdateColorRequest $request, Color $color, ColorService $colorService)
    {
        $existenceErrors = $colorService->existenceCheck($color);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $uniquenessErrors = $colorService->uniquenessChecks($request, $color);
        if ($uniquenessErrors) {
            return $uniquenessErrors;
        }

        $input = $request->all();

        if (isset($input['code'])) {
            $color->code = $input['code'];
            $color->updated_by = $input['updatedBy'];
        }
        $color->save();

        if (!isset($input['translations'])) {
            return response()->json([
                "message" => "Color updated successfully"
            ]);
        }

        foreach ($input['translations'] as $trans) {

            $translation = $color->translations()
                ->where('language_id', $trans['languageId'])
                ->first();

            //if not exist , else if deleted, else need to update
            if (!$translation) {
                $color->translations()->create([
                    'language_id' => $trans['languageId'],
                    'name' => $trans['name'],
                    'created_by' => $input['updatedBy'],
                ]);
            } elseif (!is_null($translation->deleted_by)) {
                $translation->update([
                    'name' => $trans['name'],
                    'updated_by' => $input['updatedBy'],
                    'deleted_by' => null,
                    'deleted_at' => null,
                ]);
            } else {
                $translation->update([
                    'name' => $trans['name'],
                    'updated_by' => $input['updatedBy']
                ]);
            }
        }

        return response()->json([
            "message" => "Color updated successfully"
        ]);
    }

    public function destroy(DestroyColorRequest $request, Color $color, ColorService $colorService)
    {
        $existenceErrors = $colorService->existenceCheck($color);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $color->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);

        ColorTranslation::where('color_id', $color->id)->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);


        return response()->json([
            'message' => 'Size deleted successfully',
        ]);

    }
}
