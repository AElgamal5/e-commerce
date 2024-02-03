<?php

namespace App\Http\Controllers\api\v1;

use App\Models\Tag;
use App\Models\Language;
use App\Filters\v1\TagFilter;
use App\Models\TagTranslation;
use App\Services\v1\TagService;
use App\Http\Controllers\Controller;
use App\Services\v1\LanguageService;
use Illuminate\Support\Facades\Redis;
use App\Http\Resources\v1\TagResource;
use App\Filters\v1\TagTranslationFilter;
use App\Http\Resources\v1\TagCollection;
use App\Http\Requests\v1\Tag\ShowTagRequest;
use App\Http\Requests\v1\Tag\IndexTagRequest;
use App\Http\Requests\v1\Tag\StoreTagRequest;
use App\Http\Requests\v1\Tag\UpdateTagRequest;
use App\Http\Requests\v1\Tag\DestroyTagRequest;

class TagController extends Controller
{
    public function index(IndexTagRequest $request)
    {

        $key = $this->generateCacheKey('tags', $request->page ?? 1, $request->PageSize ?? 15, $request->all());

        if (Redis::exists($key)) {
            $tags = Redis::get($key);
            return unserialize($tags);
        }

        //tag filter
        $filter = new TagFilter();
        $filterItems = $filter->transform($request);

        $tags = Tag::search($request->search)->where('deleted_by', null)->where($filterItems);

        //tag translation filter
        $translationFilter = new TagTranslationFilter();
        $translationFilterItems = $translationFilter->transform($request);
        $tagTranslations = TagTranslation::where('deleted_by', null)->where($translationFilterItems)->get('tag_id');
        $tags->whereIn('id', $tagTranslations->toArray());

        if ($request->query('createdByUser') == 'true') {
            $tags = $tags->with('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $tags = $tags->with('updatedByUser');
        }

        if ($request->query('translations') == '*') {
            $tags = $tags->with('translations.language');
        } elseif ($request->has('translations')) {

            $langId = $request->query('translations');

            $tags = $tags->with([
                'translations' => function ($query) use ($langId) {
                    $query->where('language_id', $langId)->where('deleted_by', null);
                }
            ]);
        } elseif ($request->has('lang')) {
            $langCode = $request->query('lang');
            $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
            $language = $language ? $language->toArray() : null;

            if ($language) {
                $tags = $tags->with([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language['id'])->where('deleted_by', null);
                    }
                ]);
            }
        }

        if ($request->pageSize == -1) {
            $tags = new TagCollection($tags->get());
        } else {
            $tags = new TagCollection($tags->paginate($request->pageSize)->appends($request->query()));
        }

        Redis::set($key, serialize($tags));

        return $tags;
    }

    public function store(
        StoreTagRequest $request,
        LanguageService $languageService
    ) {
        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $tag = Tag::create($request->all());

        $tag->saveTranslations($request->all());

        return new TagResource($tag);
    }

    public function show(ShowTagRequest $request, Tag $tag, TagService $tagService)
    {

        $existenceErrors = $tagService->existenceCheck($tag);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $key = $this->generateCacheKeyForOne('tags', $tag->id, $request->all());

        if (Redis::exists($key)) {
            $result = Redis::get($key);
            return unserialize($result);
        }

        if ($request->query('createdByUser') == 'true') {
            $tag = $tag->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $tag = $tag->loadMissing('updatedByUser');
        }

        if ($request->query('translations') == '*') {
            $tag = $tag->loadMissing('translations.language');
        } elseif ($request->has('translations')) {

            $langId = $request->query('translations');

            $tag = $tag->loadMissing([
                'translations' => function ($query) use ($langId) {
                    $query->where('language_id', $langId)->where('deleted_by', null);
                }
            ]);
        } elseif ($request->has('lang')) {
            $langCode = $request->query('lang');
            $language = Language::where('code', $langCode)->where('deleted_by', null)->first();
            $language = $language ? $language->toArray() : null;

            if ($language) {
                $tag = $tag->loadMissing([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language['id'])->where('deleted_by', null);
                    }
                ]);
            }
        }

        $result = new TagResource($tag);

        Redis::set($key, serialize($result));

        return $result;
    }

    public function update(
        UpdateTagRequest $request,
        Tag $tag,
        TagService $tagService,
        LanguageService $languageService
    ) {
        $existenceErrors = $tagService->existenceCheck($tag);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $tag->updateTranslations($request->all());
        $tag->update($request->all());

        return response()->json([
            "message" => "Tag updated successfully"
        ]);
    }

    public function destroy(
        DestroyTagRequest $request,
        Tag $tag,
        TagService $tagService
    ) {
        $existenceErrors = $tagService->existenceCheck($tag);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $tag->update($request->all());
        $tag->deleteTranslations($request->all());

        return response()->json([
            'message' => 'Tag deleted successfully',
        ]);

    }
}
