<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Language;
use App\Models\User;
use App\Filters\v1\LanguageFilter;
use App\Http\Resources\v1\LanguageCollection;
use App\Http\Resources\v1\LanguageResource;
use App\Http\Requests\v1\Language\IndexLanguageRequest;
use App\Http\Requests\v1\Language\StoreLanguageRequest;
use App\Http\Requests\v1\Language\UpdateLanguageRequest;
use App\Http\Requests\v1\Language\DestroyLanguageRequest;

class LanguageController extends Controller
{
    public function index(IndexLanguageRequest $request)
    {
        $filter = new LanguageFilter();
        $filterItems = $filter->transform($request);

        $langs = Language::where('deleted_by', '=', null)->where($filterItems);

        if ($request->query('createdByUser') == 'true') {
            $langs = $langs->with('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $langs = $langs->with('updatedByUser');
        }


        return new LanguageCollection($langs->paginate()->appends($request->query()));
    }

    public function store(StoreLanguageRequest $request)
    {

        //created_by check
        $createdBy = $request->json('createdBy');
        if ($createdBy) {
            $createdByUser = User::where('id', $createdBy)->first();

            if ($createdByUser->deleted_By || $createdByUser->deleted_at) {
                return response()->json([
                    'errors' => [
                        'createdBy' => [
                            'message' => 'This user has been deleted'
                        ]
                    ]
                ], Response::HTTP_CONFLICT);
            }
        }

        return new LanguageResource(Language::create($request->all()));
    }

    public function show(Request $request, Language $language)
    {
        if ($request->query('createdByUser') == 'true') {
            $language = $language->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $language = $language->loadMissing('updatedByUser');
        }

        return new LanguageResource($language);
    }

    public function update(UpdateLanguageRequest $request, Language $language)
    {
        //updated_By check
        $updatedBy = $request->json('updatedBy');
        if ($updatedBy) {

            $updatedByUser = User::where('id', $updatedBy)->first();

            if ($updatedByUser->deleted_By || $updatedByUser->deleted_at) {

                return response()->json([
                    'errors' => [
                        'updatedBy' => [
                            'message' => 'This user has been deleted'
                        ]
                    ]
                ], Response::HTTP_CONFLICT);
            }

        }

        $language->update($request->all());

        return response()->json([
            'message' => 'Languages updated successfully'
        ]);
    }

    public function destroy(DestroyLanguageRequest $request, Language $language)
    {
        //deleted_By check
        $deletedBy = $request->json('deletedBy');
        if ($deletedBy) {

            $deletedByUser = User::where('id', $deletedBy)->first();

            if ($deletedByUser->deleted_By || $deletedByUser->deleted_at) {

                return response()->json([
                    'errors' => [
                        'deletedBy' => [
                            'message' => 'This user has been deleted'
                        ]
                    ]
                ], Response::HTTP_CONFLICT);
            }

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
