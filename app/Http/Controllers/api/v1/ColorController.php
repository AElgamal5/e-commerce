<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;

use App\Models\Color;
use App\Models\Language;
use App\Models\ColorTranslation;

use App\Filters\v1\ColorFilter;
use App\Filters\v1\ColorTranslationFilter;

use App\Http\Resources\v1\ColorCollection;
use App\Http\Resources\v1\ColorResource;

use App\Http\Requests\v1\Color\IndexColorRequest;
use App\Http\Requests\v1\Color\StoreColorRequest;
use App\Http\Requests\v1\Color\ShowColorRequest;
use App\Http\Requests\v1\Color\UpdateColorRequest;
use App\Http\Requests\v1\Color\DestroyColorRequest;

use App\Services\v1\ColorService;
use App\Services\v1\LanguageService;

class ColorController extends Controller
{
    public function index(IndexColorRequest $request)
    {
        //color filter
        $filter = new ColorFilter();
        $filterItems = $filter->transform($request);

        $colors = Color::search($request->search)->where('deleted_by', null)->where($filterItems);

        //color translation filter
        $translationFilter = new ColorTranslationFilter();
        $translationFilterItems = $translationFilter->transform($request);
        $colorTranslations = ColorTranslation::where('deleted_by', null)->where($translationFilterItems)->get('color_id');
        $colors->whereIn('id', $colorTranslations->toArray());

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
        } elseif ($request->has('lang')) {
            $langCode = $request->query('lang');
            $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
            $language = $language ? $language->toArray() : null;

            if ($language) {
                $colors = $colors->with([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language['id'])->where('deleted_by', null);
                    }
                ]);
            }
        }

        if ($request->pageSize == -1) {
            return new ColorCollection($colors->get());
        }

        return new ColorCollection($colors->paginate($request->pageSize)->appends($request->query()));
    }

    public function store(
        StoreColorRequest $request,
        ColorService $colorService,
        LanguageService $languageService
    ) {
        $uniquenessErrors = $colorService->uniquenessChecks($request);
        if ($uniquenessErrors) {
            return $uniquenessErrors;
        }

        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $color = Color::create($request->all());
        $color->saveTranslations($request->all());

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
        } elseif ($request->has('lang')) {
            $langCode = $request->query('lang');
            $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
            $language = $language ? $language->toArray() : null;

            if ($language) {
                $color = $color->loadMissing([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language['id'])->where('deleted_by', null);
                    }
                ]);
            }
        }

        return new ColorResource($color);
    }

    public function update(
        UpdateColorRequest $request,
        Color $color,
        ColorService $colorService,
        LanguageService $languageService
    ) {
        $existenceErrors = $colorService->existenceCheck($color);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $uniquenessErrors = $colorService->uniquenessChecks($request, $color);
        if ($uniquenessErrors) {
            return $uniquenessErrors;
        }

        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $color->update($request->all());

        if ($request->has('translations')) {
            $color->updateTranslations($request->all());
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

        $color->update($request->all());
        $color->deleteTranslations($request->all());

        return response()->json([
            'message' => 'Color deleted successfully',
        ]);

    }
}
