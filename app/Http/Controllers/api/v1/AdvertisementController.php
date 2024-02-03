<?php

namespace App\Http\Controllers\api\v1;

use App\Models\Advertisement;
use App\Services\v1\ImageService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Filters\v1\AdvertisementFilter;
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

        $key = $this->generateCacheKey('advertisements', $request->page ?? 1, $request->PageSize ?? 15, $request->all());

        if (Redis::exists($key)) {
            $advertisements = Redis::get($key);
            return unserialize($advertisements);
        }

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
            $advertisements = new AdvertisementCollection($advertisements->get());
        } else {
            $advertisements = new AdvertisementCollection($advertisements->paginate($request->pageSize)->appends($request->query()));
        }

        Redis::set($key, serialize($advertisements));

        return $advertisements;
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

        $key = $this->generateCacheKeyForOne('advertisements', $advertisement->id, $request->all());

        if (Redis::exists($key)) {
            $result = Redis::get($key);
            return unserialize($result);
        }

        if ($request->query('createdByUser') == 'true') {
            $advertisement = $advertisement->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $advertisement = $advertisement->loadMissing('updatedByUser');
        }

        $result = new AdvertisementResource($advertisement);

        Redis::set($key, serialize($result));

        return $result;
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
