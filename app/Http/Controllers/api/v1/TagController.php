<?php

namespace App\Http\Controllers\api\v1;

use Illuminate\Http\Response;

use App\Models\Tag;
use App\Models\Language;
use App\Filters\v1\TagFilter;

use App\Models\TagTranslation;
use App\Http\Controllers\Controller;

use App\Services\v1\TagService;
use App\Services\v1\LanguageService;

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
        //tag filter
        $filter = new TagFilter();
        $filterItems = $filter->transform($request);

        $categories = Tag::where('deleted_by', null)->where($filterItems);

        //tag translation filter
        $translationFilter = new TagTranslationFilter();
        $translationFilterItems = $translationFilter->transform($request);
        $tagTranslations = TagTranslation::where('deleted_by', null)->where($translationFilterItems)->get('tag_id');
        $categories->whereIn('id', $tagTranslations->toArray());

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

        return new TagCollection($categories->paginate()->appends($request->query()));
    }

    public function store(
        StoreTagRequest $request,
        LanguageService $languageService
    ) {
        $translationsErrors = $languageService->translationsCheck($request);
        if ($translationsErrors) {
            return $translationsErrors;
        }

        $input = $request->all();

        $tag = Tag::create([
            'created_by' => $input['createdBy'],
        ]);

        foreach ($input['translations'] as $trans) {
            $tag->translations()->create([
                'language_id' => $trans['languageId'],
                'name' => $trans['name'],
                'description' => $trans['description'] ?? null,
                'created_by' => $input['createdBy'],
            ]);
        }

        return new TagResource($tag);
    }

    public function show(ShowTagRequest $request, Tag $tag, TagService $tagService)
    {

        $existenceErrors = $tagService->existenceCheck($tag);
        if ($existenceErrors) {
            return $existenceErrors;
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

        return new TagResource($tag);
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

        $input = $request->all();

        if (!isset($input['translations'])) {
            return response()->json([
                "message" => "Tag updated successfully"
            ]);
        }

        foreach ($input['translations'] as $trans) {

            $translation = $tag->translations()
                ->where('language_id', $trans['languageId'])
                ->first();

            //if not exist , else if deleted then respawn, else need to update
            if (!$translation) {
                //need at least name to create a new translation
                if (!isset($trans['name'])) {
                    return response()
                        ->json(
                            ['message' => "Name is required to create a new tag translations"],
                            Response::HTTP_NOT_FOUND
                        );
                }

                $tag->translations()->create([
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

        $tag->updated_by = $input['updatedBy'];
        $tag->save();

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

        $tag->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);

        TagTranslation::where('tag_id', $tag->id)->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);


        return response()->json([
            'message' => 'Tag deleted successfully',
        ]);

    }
}
