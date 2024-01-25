<?php

namespace App\Http\Controllers\api\v1;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

use App\Models\Category;
use App\Models\Language;

use App\Filters\v1\CategoryFilter;
use App\Filters\v1\CategoryTranslationFilter;

use App\Models\CategoryTranslation;

use App\Services\v1\CategoryService;
use App\Services\v1\LanguageService;
use App\Services\v1\ImageService;

use App\Http\Resources\v1\CategoryResource;
use App\Http\Resources\v1\CategoryCollection;

use App\Http\Requests\v1\Category\ShowCategoryRequest;
use App\Http\Requests\v1\Category\IndexCategoryRequest;
use App\Http\Requests\v1\Category\StoreCategoryRequest;
use App\Http\Requests\v1\Category\UpdateCategoryRequest;
use App\Http\Requests\v1\Category\DestroyCategoryRequest;

class CategoryController extends Controller
{
    public function index(IndexCategoryRequest $request)
    {
        //category filter
        $filter = new CategoryFilter();
        $filterItems = $filter->transform($request);

        $categories = Category::search($request->search)->where('deleted_by', null)->where($filterItems);

        //category translation filter
        $translationFilter = new CategoryTranslationFilter();
        $translationFilterItems = $translationFilter->transform($request);
        $categoryTranslations = CategoryTranslation::where('deleted_by', null)->where($translationFilterItems)->get('category_id');
        $categories->whereIn('id', $categoryTranslations->toArray());

        if ($request->query('createdByUser') == 'true') {
            $categories = $categories->with('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $categories = $categories->with('updatedByUser');
        }

        if ($request->query('translations') == '*') {
            $categories = $categories->with('translations.language');
        } elseif ($request->has('translations')) {

            $langId = $request->query('translations');

            $categories = $categories->with([
                'translations' => function ($query) use ($langId) {
                    $query->where('language_id', $langId)->where('deleted_by', null);
                }
            ]);
        } elseif ($request->has('lang')) {
            $langCode = $request->query('lang');
            $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
            $language = $language ? $language->toArray() : null;

            if ($language) {
                $categories = $categories->with([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language['id'])->where('deleted_by', null);
                    }
                ]);
            }
        }

        if ($request->pageSize == -1) {
            return new CategoryCollection($categories->get());
        }

        return new CategoryCollection($categories->paginate($request->pageSize)->appends($request->query()));
    }

    public function store(
        StoreCategoryRequest $request,
        LanguageService $languageService,
        ImageService $imageService
    ) {
        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        //save the image to the disk
        $trickOrTreat = $imageService->save($request->image);
        if ($trickOrTreat instanceof JsonResponse) {
            return $trickOrTreat;
        } else {
            $request->merge(['image' => $trickOrTreat]);
        }

        $category = Category::create($request->all());

        $category->saveTranslations($request->all());

        return new CategoryResource($category);
    }

    public function show(ShowCategoryRequest $request, Category $category, CategoryService $categoryService)
    {

        $existenceErrors = $categoryService->existenceCheck($category);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        if ($request->query('createdByUser') == 'true') {
            $category = $category->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $category = $category->loadMissing('updatedByUser');
        }

        if ($request->query('translations') == '*') {
            $category = $category->loadMissing('translations.language');
        } elseif ($request->has('translations')) {

            $langId = $request->query('translations');

            $category = $category->loadMissing([
                'translations' => function ($query) use ($langId) {
                    $query->where('language_id', $langId)->where('deleted_by', null);
                }
            ]);
        } elseif ($request->has('lang')) {
            $langCode = $request->query('lang');
            $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
            $language = $language ? $language->toArray() : null;

            if ($language) {
                $category = $category->loadMissing([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language['id'])->where('deleted_by', null);
                    }
                ]);
            }
        }

        return new CategoryResource($category);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        CategoryService $categoryService,
        LanguageService $languageService,
        ImageService $imageService
    ) {
        $existenceErrors = $categoryService->existenceCheck($category);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        if ($request->has('image')) {
            //delete the old image
            $imageService->delete($category->image);
            //save the new one
            $imageName = $imageService->save($request->image);

            $request->merge(['image' => $imageName]);

            $category->update($request->all());
        }

        if ($request->has('translations')) {
            $category->updateTranslations($request->all());
        }

        return response()->json([
            "message" => "Category updated successfully"
        ]);
    }

    public function destroy(
        DestroyCategoryRequest $request,
        Category $category,
        CategoryService $categoryService,
        ImageService $imageService
    ) {
        $existenceErrors = $categoryService->existenceCheck($category);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $imageService->delete($category->image);

        $category->update($request->all());
        $category->deleteTranslations($request->all());

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
