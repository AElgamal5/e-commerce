<?php

namespace App\Http\Controllers\api\v1;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

use App\Models\Category;
use App\Models\Language;
use App\Filters\v1\CategoryFilter;

use App\Models\CategoryTranslation;
use App\Http\Controllers\Controller;

use App\Services\v1\CategoryService;
use App\Services\v1\LanguageService;

use App\Http\Resources\v1\CategoryResource;
use App\Filters\v1\CategoryTranslationFilter;
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

        $categories = Category::where('deleted_by', null)->where($filterItems);

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

        return new CategoryCollection($categories->paginate()->appends($request->query()));
    }

    public function store(
        StoreCategoryRequest $request,
        LanguageService $languageService
    ) {
        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $input = $request->all();

        //save the image to the disk
        $imageData = base64_decode(preg_replace('/^data:image\/(\w+);base64,/', '', $input['image']));
        $uniqueId = uniqid();
        $imageName = time() . '_' . $uniqueId . '.png';
        Storage::disk('public')->put($imageName, $imageData);

        $category = Category::create([
            'image' => $imageName ?? null,
            'created_by' => $input['createdBy'],
        ]);

        foreach ($input['translations'] as $trans) {
            $category->translations()->create([
                'language_id' => $trans['languageId'],
                'name' => $trans['name'],
                'description' => $trans['description'] ?? null,
                'created_by' => $input['createdBy'],
            ]);
        }

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
        LanguageService $languageService
    ) {
        $existenceErrors = $categoryService->existenceCheck($category);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $input = $request->all();

        if (isset($input['image'])) {
            //delete the old image
            //save the new one
        }
        $category->save();

        if (!isset($input['translations'])) {
            return response()->json([
                "message" => "Category updated successfully"
            ]);
        }

        foreach ($input['translations'] as $trans) {

            $translation = $category->translations()
                ->where('language_id', $trans['languageId'])
                ->first();

            //if not exist , else if deleted then respawn, else need to update
            if (!$translation) {
                //need at least name to create a new translation
                if (!isset($trans['name'])) {
                    return response()
                        ->json(
                            ['message' => "Name is required to create a new category translations"],
                            Response::HTTP_NOT_FOUND
                        );
                }

                $category->translations()->create([
                    'language_id' => $trans['languageId'],
                    'name' => $trans['name'],
                    'description' => $trans['description'] ?? null,
                    'created_by' => $input['updatedBy'],
                ]);
            } elseif (!is_null($translation->deleted_by)) {
                $translation->update([
                    'name' => $trans['name'] ?? $translation->name,
                    'description' => $trans['description'] ?? $translation->description,
                    'updated_by' => $input['updatedBy'],
                    'deleted_by' => null,
                    'deleted_at' => null,
                ]);
            } else {
                $translation->update([
                    'name' => $trans['name'] ?? $translation->name,
                    'description' => $trans['description'] ?? $translation->description,
                    'updated_by' => $input['updatedBy']
                ]);
            }
        }

        return response()->json([
            "message" => "Category updated successfully"
        ]);
    }

    public function destroy(DestroyCategoryRequest $request, Category $category, CategoryService $categoryService)
    {
        $existenceErrors = $categoryService->existenceCheck($category);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $category->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);

        CategoryTranslation::where('category_id', $category->id)->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);


        return response()->json([
            'message' => 'Category deleted successfully',
        ]);

    }
}
