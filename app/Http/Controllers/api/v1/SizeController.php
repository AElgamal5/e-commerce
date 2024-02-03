<?php

namespace App\Http\Controllers\api\v1;

use App\Models\Size;

use App\Filters\v1\SizeFilter;

use App\Services\v1\SizeService;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

use App\Http\Resources\v1\SizeResource;
use App\Http\Resources\v1\SizeCollection;
use App\Http\Requests\v1\Size\ShowSizeRequest;
use App\Http\Requests\v1\Size\IndexSizeRequest;
use App\Http\Requests\v1\Size\StoreSizeRequest;

use App\Http\Requests\v1\Size\UpdateSizeRequest;
use App\Http\Requests\v1\Size\DestroySizeRequest;

class SizeController extends Controller
{
    public function index(IndexSizeRequest $request)
    {

        $key = $this->generateCacheKey('sizes', $request->page ?? 1, $request->PageSize ?? 15, $request->all());

        if (Redis::exists($key)) {
            $langs = Redis::get($key);
            return unserialize($langs);
        }

        $filter = new SizeFilter();
        $filterItems = $filter->transform($request);

        $sizes = Size::search($request->search)->where('deleted_by', '=', null)->where($filterItems);

        if ($request->query('createdByUser') == 'true') {
            $sizes = $sizes->with('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $sizes = $sizes->with('updatedByUser');
        }

        if ($request->pageSize == -1) {
            $sizes = new SizeCollection($sizes->get());
        } else {
            $sizes = new SizeCollection($sizes->paginate($request->pageSize)->appends($request->query()));
        }

        Redis::set($key, serialize($sizes));

        return $sizes;
    }

    public function store(StoreSizeRequest $request, SizeService $sizeService)
    {
        $uniquenessErrors = $sizeService->uniquenessChecks($request);
        if ($uniquenessErrors) {
            return $uniquenessErrors;
        }

        return new SizeResource(Size::create($request->all()));
    }

    public function show(ShowSizeRequest $request, Size $size, SizeService $sizeService)
    {
        $existenceErrors = $sizeService->existenceCheck($size);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $key = $this->generateCacheKeyForOne('sizes', $size->id, $request->all());

        if (Redis::exists($key)) {
            $result = Redis::get($key);
            return unserialize($result);
        }

        if ($request->query('createdByUser') == 'true') {
            $size = $size->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $size = $size->loadMissing('updatedByUser');
        }

        $result = new SizeResource($size);

        Redis::set($key, serialize($result));

        return $result;
    }

    public function update(UpdateSizeRequest $request, Size $size, SizeService $sizeService)
    {
        $existenceErrors = $sizeService->existenceCheck($size);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $uniquenessErrors = $sizeService->uniquenessChecks($request, $size);
        if ($uniquenessErrors) {
            return $uniquenessErrors;
        }

        $size->update($request->all());

        return response()->json([
            'message' => 'Sizes updated successfully'
        ]);
    }

    public function destroy(DestroySizeRequest $request, Size $size, SizeService $sizeService)
    {
        $existenceErrors = $sizeService->existenceCheck($size);
        if ($existenceErrors) {
            return $existenceErrors;
        }

        $size->update($request->all());

        return response()->json([
            'message' => 'Size deleted successfully',
        ]);
    }
}
