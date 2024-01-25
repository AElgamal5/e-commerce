<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;

use App\Models\Size;

use App\Filters\v1\SizeFilter;

use App\Http\Resources\v1\SizeCollection;
use App\Http\Resources\v1\SizeResource;

use App\Http\Requests\v1\Size\IndexSizeRequest;
use App\Http\Requests\v1\Size\StoreSizeRequest;
use App\Http\Requests\v1\Size\ShowSizeRequest;
use App\Http\Requests\v1\Size\UpdateSizeRequest;
use App\Http\Requests\v1\Size\DestroySizeRequest;

use App\Services\v1\SizeService;

class SizeController extends Controller
{
    public function index(IndexSizeRequest $request)
    {
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
            return new SizeCollection($sizes->get());
        }

        return new SizeCollection($sizes->paginate($request->pageSize)->appends($request->query()));
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

        if ($request->query('createdByUser') == 'true') {
            $size = $size->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $size = $size->loadMissing('updatedByUser');
        }

        return new SizeResource($size);
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
