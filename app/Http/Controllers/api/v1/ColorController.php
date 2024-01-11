<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Color;
use App\Models\Language;

use App\Filters\v1\ColorFilter;

use App\Http\Resources\v1\ColorCollection;
use App\Http\Resources\v1\ColorResource;

use App\Http\Requests\v1\Color\IndexColorRequest;
use App\Http\Requests\v1\Color\StoreColorRequest;
use App\Http\Requests\v1\Color\ShowColorRequest;
use App\Http\Requests\v1\Color\UpdateColorRequest;
use App\Http\Requests\v1\Color\DestroyColorRequest;

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
            $colors = $colors->with('translations');
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

    public function store(StoreColorRequest $request)
    {
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

    public function show(ShowColorRequest $request, Color $color)
    {
        if ($request->query('createdByUser') == 'true') {
            $color = $color->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $color = $color->loadMissing('updatedByUser');
        }

        if ($request->query('translations') == '*') {
            $color = $color->loadMissing('translations');
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

    public function update(Request $request, Color $color)
    {
        //
    }

    public function destroy(Request $request, Color $color)
    {
        //
    }
}
