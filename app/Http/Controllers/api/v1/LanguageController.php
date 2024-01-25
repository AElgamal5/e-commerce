<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;

use App\Models\Language;

use App\Filters\v1\LanguageFilter;

use App\Http\Resources\v1\LanguageCollection;
use App\Http\Resources\v1\LanguageResource;

use App\Http\Requests\v1\Language\IndexLanguageRequest;
use App\Http\Requests\v1\Language\StoreLanguageRequest;
use App\Http\Requests\v1\Language\ShowLanguageRequest;
use App\Http\Requests\v1\Language\UpdateLanguageRequest;
use App\Http\Requests\v1\Language\DestroyLanguageRequest;

use App\Services\v1\LanguageService;

class LanguageController extends Controller
{
    public function index(IndexLanguageRequest $request)
    {
        $filter = new LanguageFilter();
        $filterItems = $filter->transform($request);

        $langs = Language::search($request->search)->where('deleted_by', '=', null)->where($filterItems);

        if ($request->query('createdByUser') == 'true') {
            $langs = $langs->with('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $langs = $langs->with('updatedByUser');
        }

        if ($request->pageSize == -1) {
            return new LanguageCollection($langs->get());
        }

        return new LanguageCollection($langs->paginate($request->pageSize)->appends($request->query()));
    }

    public function store(StoreLanguageRequest $request, LanguageService $languageService)
    {
        $uniquenessErrors = $languageService->uniquenessChecks($request);
        if ($uniquenessErrors) {
            return $uniquenessErrors;
        }

        return new LanguageResource(Language::create($request->all()));
    }

    public function show(ShowLanguageRequest $request, Language $language, LanguageService $languageService)
    {
        $existenceErrors = $languageService->existenceCheck($language);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        if ($request->query('createdByUser') == 'true') {
            $language = $language->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $language = $language->loadMissing('updatedByUser');
        }

        return new LanguageResource($language);
    }

    public function update(UpdateLanguageRequest $request, Language $language, LanguageService $languageService)
    {
        $existenceErrors = $languageService->existenceCheck($language);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $uniquenessErrors = $languageService->uniquenessChecks($request);
        if ($uniquenessErrors) {
            return $uniquenessErrors;
        }

        $language->update($request->all());

        return response()->json([
            'message' => 'Languages updated successfully'
        ]);
    }

    public function destroy(DestroyLanguageRequest $request, Language $language, LanguageService $languageService)
    {
        $existenceErrors = $languageService->existenceCheck($language);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $language->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Language deleted successfully',
        ]);
    }
}
