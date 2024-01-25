<?php

namespace App\Http\Controllers\api\v1;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

use App\Models\Advertisement;
use App\Filters\v1\AdvertisementFilter;

use App\Services\v1\ImageService;

use App\Http\Resources\v1\AdvertisementResource;
use App\Http\Resources\v1\AdvertisementCollection;

use App\Http\Requests\v1\Advertisement\ShowAdvertisementRequest;
use App\Http\Requests\v1\Advertisement\IndexAdvertisementRequest;
use App\Http\Requests\v1\Advertisement\StoreAdvertisementRequest;
use App\Http\Requests\v1\Advertisement\UpdateAdvertisementRequest;
use App\Http\Requests\v1\Advertisement\DestroyAdvertisementRequest;

class AdvertisementController extends Controller
{
    public function index(IndexAdvertisementRequest $request)
    {
        //advertisement filter
        $filter = new AdvertisementFilter();
        $filterItems = $filter->transform($request);

        $advertisements = Advertisement::search($request->search)->where('deleted_by', null)->where($filterItems);


        if ($request->query('createdByUser') == 'true') {
            $advertisements = $advertisements->with('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $advertisements = $advertisements->with('updatedByUser');
        }

        if ($request->pageSize == -1) {
            return new AdvertisementCollection($advertisements->get());
        }

        return new AdvertisementCollection($advertisements->paginate($request->pageSize)->appends($request->query()));
    }

    public function store(
        StoreAdvertisementRequest $request,
        ImageService $imageService
    ) {

        //save the image to the disk
        $trickOrTreat = $imageService->save($request->image);
        if ($trickOrTreat instanceof JsonResponse) {
            return $trickOrTreat;
        } else {
            $request->merge(['image' => $trickOrTreat]);
        }

        $advertisement = Advertisement::create($request->all());

        return new AdvertisementResource($advertisement);
    }

    public function show(
        ShowAdvertisementRequest $request,
        Advertisement $advertisement,
    ) {
        if ($request->query('createdByUser') == 'true') {
            $advertisement = $advertisement->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $advertisement = $advertisement->loadMissing('updatedByUser');
        }

        return new AdvertisementResource($advertisement);
    }

    public function update(
        UpdateAdvertisementRequest $request,
        Advertisement $advertisement,
        ImageService $imageService
    ) {
        if ($request->has('image')) {
            //delete the old image
            $imageService->delete($advertisement->image);
            //save the new one
            $imageName = $imageService->save($request->image);

            $request->merge(['image' => $imageName]);

            $advertisement->update($request->all());
        }

        return response()->json([
            "message" => "Advertisement updated successfully"
        ]);
    }

    public function destroy(
        DestroyAdvertisementRequest $request,
        Advertisement $advertisement,
        ImageService $imageService
    ) {

        $imageService->delete($advertisement->image);

        $advertisement->update($request->all());

        return response()->json([
            'message' => 'Advertisement deleted successfully',
        ]);
    }
}
