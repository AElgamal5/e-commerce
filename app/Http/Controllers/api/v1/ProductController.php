<?php

namespace App\Http\Controllers\api\v1;

use App\Models\Product;
use App\Models\Language;
use App\Models\ProductTag;
use App\Models\ProductImage;
use Illuminate\Http\Response;
use App\Models\ProductQuantity;
use App\Services\v1\TagService;
use App\Services\v1\SizeService;
use App\Filters\v1\ProductFilter;
use App\Services\v1\ColorService;
use App\Services\v1\ImageService;
use App\Models\ProductTranslation;
use App\Services\v1\ProductService;
use App\Filters\v1\ProductTagFilter;
use App\Http\Controllers\Controller;
use App\Services\v1\CategoryService;
use App\Services\v1\LanguageService;
use Illuminate\Support\Facades\Redis;
use App\Filters\v1\ProductImageFilter;
use App\Filters\v1\ProductQuantityFilter;
use App\Http\Resources\v1\ProductResource;
use App\Filters\v1\ProductTranslationFilter;
use App\Http\Resources\v1\ProductCollection;
use App\Http\Requests\v1\Product\ShowProductRequest;
use App\Http\Requests\v1\Product\IndexProductRequest;
use App\Http\Requests\v1\Product\StoreProductRequest;
use App\Http\Requests\v1\Product\UpdateProductRequest;
use App\Http\Requests\v1\Product\DestroyProductRequest;
use App\Http\Requests\v1\Product\AddImagesToProductRequest;
use App\Http\Requests\v1\Product\AddQuantitiesToProductRequest;
use App\Http\Requests\v1\Product\DeleteImagesFromProductRequest;
use App\Http\Requests\v1\Product\deleteQuantitiesToProductRequest;

class ProductController extends Controller
{
    public function index(IndexProductRequest $request)
    {
        $key = $this->generateCacheKey('products', $request->page ?? 1, $request->PageSize ?? 15, $request->all());

        if (Redis::exists($key)) {
            $products = Redis::get($key);
            return unserialize($products);
        }

        //product filter
        $filter = new ProductFilter();
        $filterItems = $filter->transform($request);
        $products = $products = Product::where('deleted_by', null)->where($filterItems);

        //product translation filter
        $translationFilter = new ProductTranslationFilter();
        $translationFilterItems = $translationFilter->transform($request);
        if (count($translationFilterItems)) {
            $productTranslations = ProductTranslation::where('deleted_by', null)->where($translationFilterItems)->get('product_id');
            $products->whereIn('id', $productTranslations->toArray());
        }

        //product tag filter
        $tagFilter = new ProductTagFilter();
        $tagFilterItems = $tagFilter->transform($request);
        if (count($tagFilterItems)) {
            $productTags = ProductTag::where('deleted_by', null)->where($tagFilterItems)->get('product_id');
            $products->whereIn('id', $productTags->toArray());
        }

        //product image filter
        $tagFilter = new ProductImageFilter();
        $productImageFilterItems = $tagFilter->transform($request);
        if (count($productImageFilterItems)) {
            $productImages = ProductImage::where($productImageFilterItems)->get('product_id');
            $products->whereIn('id', $productImages->toArray());
        }

        //product image filter
        $tagFilter = new ProductQuantityFilter();
        $productQuantitiesFilterItems = $tagFilter->transform($request);
        if (count($productQuantitiesFilterItems)) {
            $productQuantities = ProductQuantity::where('deleted_by', null)->where($productQuantitiesFilterItems)->get('product_id');
            $products->whereIn('id', $productQuantities->toArray());
        }

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

        if ($request->query('tags') == 'true') {
            if ($request->has('lang')) {
                $langCode = $request->query('lang');
                $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
                $language = $language ? $language->toArray() : null;
                if ($language) {
                    $products = $products->with([
                        'tags.tag.translations' => function ($query) use ($language) {
                            $query->where('language_id', $language['id'])->where('deleted_by', null);
                        }
                    ]);
                }
            } else {
                $products = $products->with('tags.tag.translations');
            }

        }

        if ($request->query('images') == 'true') {
            if ($request->has('lang')) {
                $langCode = $request->query('lang');
                $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
                $language = $language ? $language->toArray() : null;
                if ($language) {
                    $products = $products->with([
                        'images.color.translations' => function ($query) use ($language) {
                            $query->where('language_id', $language['id'])->where('deleted_by', null);
                        }
                    ]);
                }
            } else {
                $products = $products->with('images.color.translations');
            }

        }

        if ($request->query('quantities') == 'true') {
            if ($request->has('lang')) {
                $langCode = $request->query('lang');
                $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
                $language = $language ? $language->toArray() : null;
                if ($language) {
                    $products = $products->with([
                        'quantities.color.translations' => function ($query) use ($language) {
                            $query->where('language_id', $language['id'])->where('deleted_by', null);
                        },
                        'quantities.size'
                    ]);
                }
            } else {
                $products = $products->with(['quantities.color.translations', 'quantities.size']);
            }
        }

        if ($request->pageSize == -1) {
            $products = new ProductCollection($products->get());
        } else {
            $products = new ProductCollection($products->paginate($request->pageSize)->appends($request->query()));
        }

        Redis::set($key, serialize($products));

        return $products;
    }

    public function store(
        StoreProductRequest $request,
        LanguageService $languageService,
        CategoryService $categoryService,
        TagService $tagService,
    ) {
        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $categoryExistenceErrors = $categoryService->existenceCheckById($request->categoryId);
        if ($categoryExistenceErrors) {
            return $categoryExistenceErrors;
        }

        $tagsExistenceErrors = $tagService->productTagsCheck($request);
        if ($tagsExistenceErrors) {
            return $tagsExistenceErrors;
        }

        if (
            ($request->has('discountType') && !$request->has('discountValue'))
            || (!$request->has('discountType') && $request->has('discountValue'))
        ) {
            return response()->json([
                'message' => 'Discount type & discount value are coupled',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $input = $request->all();

        $product = Product::create($input);

        foreach ($input['translations'] as $trans) {
            $product->translations()->create([
                'language_id' => $trans['languageId'],
                'name' => $trans['name'],
                'description' => $trans['description'] ?? null,
                'created_by' => $input['created_by'],
            ]);
        }

        foreach ($input['tags'] as $tag) {
            $product->tags()->create([
                'tag_id' => $tag,
                'created_by' => $input['created_by'],
            ]);
        }

        return new ProductResource($product);
    }

    public function show(
        ShowProductRequest $request,
        Product $product,
        ProductService $productService
    ) {

        $existenceErrors = $productService->existenceCheck($product);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $key = $this->generateCacheKeyForOne('products', $product->id, $request->all());

        if (Redis::exists($key)) {
            $result = Redis::get($key);
            return unserialize($result);
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

        if ($request->query('tags') == 'true') {
            if ($request->has('lang')) {
                $langCode = $request->query('lang');
                $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
                $language = $language ? $language->toArray() : null;
                if ($language) {
                    $product = $product->loadMissing([
                        'tags.tag.translations' => function ($query) use ($language) {
                            $query->where('language_id', $language['id'])->where('deleted_by', null);
                        }
                    ]);
                }
            } else {
                $product = $product->loadMissing('tags.tag.translations');
            }

        }

        if ($request->query('images') == 'true') {
            if ($request->has('lang')) {
                $langCode = $request->query('lang');
                $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
                $language = $language ? $language->toArray() : null;
                if ($language) {
                    $product = $product->loadMissing([
                        'images.color.translations' => function ($query) use ($language) {
                            $query->where('language_id', $language['id'])->where('deleted_by', null);
                        }
                    ]);
                }
            } else {
                $product = $product->loadMissing('images.color.translations');
            }

        }

        if ($request->query('quantities') == 'true') {
            if ($request->has('lang')) {
                $langCode = $request->query('lang');
                $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
                $language = $language ? $language->toArray() : null;
                if ($language) {
                    $product = $product->loadMissing([
                        'quantities.color.translations' => function ($query) use ($language) {
                            $query->where('language_id', $language['id'])->where('deleted_by', null);
                        },
                        'quantities.size'
                    ]);
                }
            } else {
                $product = $product->loadMissing(['quantities.color.translations', 'quantities.size']);
            }

        }

        $result = new ProductResource($product);

        Redis::set($key, serialize($result));

        return $result;
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        ProductService $productService,
        LanguageService $languageService,
        CategoryService $categoryService,
        TagService $tagService,
    ) {
        $existenceErrors = $productService->existenceCheck($product);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $tagsExistenceErrors = $tagService->productTagsCheck($request);
        if ($tagsExistenceErrors) {
            return $tagsExistenceErrors;
        }

        $input = $request->all();

        //discount type and discount value are coupled
        if (isset($input['discount_type'])) {
            if (!isset($product->discount_value) || !isset($input['discount_value'])) {
                unset($input['discount_type']);
            }
        }
        if (isset($input['discount_value'])) {
            if (!isset($product->discount_type) || !isset($input['discount_type'])) {
                unset($input['discount_value']);
            }
        }
        if ($request->has('discountValue') && $request->has('discountType') && is_null($input['discountValue']) && is_null($input['discountType'])) {
            $input['discount_value'] = null;
            $input['discount_type'] = null;
        }

        // category check
        if (isset($input['category_id'])) {
            $categoryExistenceErrors = $categoryService->existenceCheckById($request->categoryId);
            if ($categoryExistenceErrors) {
                return $categoryExistenceErrors;
            }
        }


        $product->update($input);

        if (isset($input['translations'])) {
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
                        'created_by' => $input['updated_by'],
                    ]);
                } elseif (!is_null($translation->deleted_by)) {
                    $translation->update([
                        'name' => $trans['name'] ?? $translation->name,
                        'description' => $trans['description'] ?? $translation->description,
                        'updated_by' => $input['updated_by'],
                        'deleted_by' => null,
                        'deleted_at' => null,
                    ]);
                } else {
                    $translation->update([
                        'name' => $trans['name'] ?? $translation->name,
                        'description' => $trans['description'] ?? $translation->description,
                        'updated_by' => $input['updated_by']
                    ]);
                }
            }
        }

        if (isset($input['tags'])) {
            $product->tags()->delete();
            foreach ($input['tags'] as $tagId) {
                $product->tags()->create([
                    'tag_id' => $tagId,
                    'created_by' => $input['updated_by'],
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
        ProductService $productService,
        ImageService $imageService,
    ) {
        $existenceErrors = $productService->existenceCheck($product);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $product->update($request->all());

        $product->tags()->update($request->all());

        $product->translations()->update($request->all());

        foreach ($product->images()->get() as $image) {
            $imageService->delete($image->image);
        }

        $product->images()->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);

    }

    public function addImages(
        AddImagesToProductRequest $request,
        Product $product,
        ProductService $productService,
        ImageService $imageService,
    ) {
        $existenceErrors = $productService->existenceCheck($product);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        foreach ($request->images as $image) {
            $imageSizeErrors = $imageService->checkOnly($image['content']);
            if ($imageSizeErrors) {
                return $imageSizeErrors;
            }
        }

        $input = $request->all();

        foreach ($input['images'] as $image) {
            $imageName = $imageService->saveOnly($image['content']);
            $product->images()->create([
                'color_id' => $image['colorId'] ?? null,
                'image' => $imageName,
                'created_by' => $request->updated_by,
            ]);
        }

        return response()->json([
            'message' => 'Images added to the product successfully',
        ]);
    }

    public function deleteImages(
        DeleteImagesFromProductRequest $request,
        Product $product,
        ProductService $productService,
        ImageService $imageService,
    ) {
        $existenceErrors = $productService->existenceCheck($product);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $input = $request->all();

        foreach ($input['images'] as $image) {
            $imageService->delete($product->images()->where('id', $image)->first()->image);
            $product->images()->where('id', $image)->delete();
        }
        $product->update(['updated_by' => $input['updated_by']]);

        return response()->json([
            'message' => 'Images deleted from the product successfully',
        ]);
    }

    public function addQuantities(
        AddQuantitiesToProductRequest $request,
        Product $product,
        ProductService $productService,
        ColorService $colorService,
        SizeService $sizeService,
    ) {

        $existenceErrors = $productService->existenceCheck($product);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        foreach ($request->quantities as $quantity) {
            $existenceErrors = $colorService->existenceCheckById($quantity['colorId']);
            if ($existenceErrors) {
                return $existenceErrors;
            }
            if (!isset($quantity['sizeId'])) {
                continue;
            }
            $existenceErrors = $sizeService->existenceCheckById($quantity['sizeId']);
            if ($existenceErrors) {
                return $existenceErrors;
            }
        }

        foreach ($request->quantities as $quantity) {
            $product->quantities()->create([
                'color_id' => $quantity['colorId'],
                'size_id' => $quantity['sizeId'] ?? null,
                'initial_quantity' => $quantity['initialQuantity'],
                'current_quantity' => $quantity['initialQuantity'],
                'created_by' => $request->created_by
            ]);
        }

        return response()->json([
            'message' => 'Quantities added to the product successfully',
        ]);
    }

    public function deleteQuantities(
        deleteQuantitiesToProductRequest $request,
        Product $product,
        ProductService $productService,
    ) {

        $existenceErrors = $productService->existenceCheck($product);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        foreach ($request->quantities as $quantity) {
            $product->quantities()->where('id', $quantity)->delete();
        }

        return response()->json([
            'message' => 'Quantities deleted to the product successfully',
        ]);
    }
}
