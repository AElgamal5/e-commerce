<?php

namespace App\Http\Controllers\api\v1;

use Illuminate\Http\Response;

use App\Models\Product;
use App\Models\Language;
use App\Filters\v1\ProductFilter;

use App\Models\ProductTranslation;
use App\Http\Controllers\Controller;

use App\Services\v1\ProductService;
use App\Services\v1\LanguageService;
use App\Services\v1\CategoryService;

use App\Http\Resources\v1\ProductResource;
use App\Filters\v1\ProductTranslationFilter;
use App\Http\Resources\v1\ProductCollection;
use App\Http\Requests\v1\Product\ShowProductRequest;
use App\Http\Requests\v1\Product\IndexProductRequest;

use App\Http\Requests\v1\Product\StoreProductRequest;
use App\Http\Requests\v1\Product\UpdateProductRequest;
use App\Http\Requests\v1\Product\DestroyProductRequest;

class ProductController extends Controller
{
    public function index(IndexProductRequest $request)
    {
        //product filter
        $filter = new ProductFilter();
        $filterItems = $filter->transform($request);

        $products = Product::where('deleted_by', null)->where($filterItems);

        //product translation filter
        $translationFilter = new ProductTranslationFilter();
        $translationFilterItems = $translationFilter->transform($request);
        $productTranslations = ProductTranslation::where('deleted_by', null)->where($translationFilterItems)->get('product_id');
        $products->whereIn('id', $productTranslations->toArray());

        if ($request->query('createdByUser') == 'true') {
            $products = $products->with('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $products = $products->with('updatedByUser');
        }

        if ($request->query('translations') == '*') {
            $products = $products->with('translations.language');
        } elseif ($request->has('translations')) {

            $langId = $request->query('translations');

            $products = $products->with([
                'translations' => function ($query) use ($langId) {
                    $query->where('language_id', $langId)->where('deleted_by', null);
                }
            ]);
        } elseif ($request->has('lang')) {
            $langCode = $request->query('lang');
            $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
            $language = $language ? $language->toArray() : null;

            if ($language) {
                $products = $products->with([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language['id'])->where('deleted_by', null);
                    }
                ]);
            }
        }

        if ($request->query('category') == 'true') {
            if ($request->has('lang')) {
                $langCode = $request->query('lang');
                $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
                $language = $language ? $language->toArray() : null;
                if ($language) {
                    $products = $products->with([
                        'category.translations' => function ($query) use ($language) {
                            $query->where('language_id', $language['id'])->where('deleted_by', null);
                        }
                    ]);
                }
            } else {
                $products = $products->with('category.translations');
            }
        }

        return new ProductCollection($products->paginate()->appends($request->query()));
    }

    public function store(
        StoreProductRequest $request,
        LanguageService $languageService,
        CategoryService $categoryService,
    ) {
        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $categoryExistenceErrors = $categoryService->existenceCheckById($request->categoryId);
        if ($categoryExistenceErrors) {
            return $categoryExistenceErrors;
        }

        if (
            ($request->has('discountType') && !$request->has('discountValue'))
            || (!$request->has('discountType') && $request->has('discountValue'))
        ) {
            return response()->json([
                'message' => 'discountType & discountValue are coupled',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $input = $request->all();

        $product = Product::create($input);

        foreach ($input['translations'] as $trans) {
            $product->translations()->create([
                'language_id' => $trans['languageId'],
                'name' => $trans['name'],
                'description' => $trans['description'] ?? null,
                'created_by' => $input['createdBy'],
            ]);
        }

        return new ProductResource($product);
    }

    public function show(ShowProductRequest $request, Product $product, ProductService $productService)
    {

        $existenceErrors = $productService->existenceCheck($product);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        if ($request->query('createdByUser') == 'true') {
            $product = $product->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $product = $product->loadMissing('updatedByUser');
        }

        if ($request->query('translations') == '*') {
            $product = $product->loadMissing('translations.language');
        } elseif ($request->has('translations')) {

            $langId = $request->query('translations');

            $product = $product->loadMissing([
                'translations' => function ($query) use ($langId) {
                    $query->where('language_id', $langId)->where('deleted_by', null);
                }
            ]);
        } elseif ($request->has('lang')) {
            $langCode = $request->query('lang');
            $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
            $language = $language ? $language->toArray() : null;

            if ($language) {
                $product = $product->loadMissing([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language['id'])->where('deleted_by', null);
                    }
                ]);
            }
        }

        if ($request->query('category') == 'true') {
            if ($request->has('lang')) {
                $langCode = $request->query('lang');
                $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
                $language = $language ? $language->toArray() : null;
                if ($language) {
                    $product = $product->loadMissing([
                        'category.translations' => function ($query) use ($language) {
                            $query->where('language_id', $language['id'])->where('deleted_by', null);
                        }
                    ]);
                }
            } else {
                $product = $product->loadMissing('category.translations');
            }
        }

        return new ProductResource($product);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        ProductService $productService,
        LanguageService $languageService,
    ) {
        $existenceErrors = $productService->existenceCheck($product);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $input = $request->all();

        if (isset($input['year'])) {

            $product->year = $input['year'];
        }
        $product->updated_by = $input['updatedBy'];
        $product->save();

        if (!isset($input['translations'])) {
            return response()->json([
                "message" => "Product updated successfully"
            ]);
        }

        foreach ($input['translations'] as $trans) {

            $translation = $product->translations()
                ->where('language_id', $trans['languageId'])
                ->first();

            //if not exist , else if deleted then respawn, else need to update
            if (!$translation) {
                //need at least name to create a new translation
                if (!isset($trans['name'])) {
                    return response()
                        ->json(
                            ['message' => "Name is required to create a new product translations"],
                            Response::HTTP_NOT_FOUND
                        );
                }

                $product->translations()->create([
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
            "message" => "Product updated successfully"
        ]);
    }

    public function destroy(
        DestroyProductRequest $request,
        Product $product,
        ProductService $productService
    ) {
        $existenceErrors = $productService->existenceCheck($product);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $product->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);

        ProductTranslation::where('product_id', $product->id)->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);


        return response()->json([
            'message' => 'Product deleted successfully',
        ]);

    }
}
