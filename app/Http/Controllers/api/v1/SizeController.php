<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Size;

use App\Filters\v1\SizeFilter;

use App\Http\Resources\v1\SizeCollection;
use App\Http\Resources\v1\SizeResource;

use App\Http\Requests\v1\Size\IndexSizeRequest;
use App\Http\Requests\v1\Size\StoreSizeRequest;
use App\Http\Requests\v1\Size\UpdateSizeRequest;
use App\Http\Requests\v1\Size\DestroySizeRequest;

class SizeController extends Controller
{
    public function index(IndexSizeRequest $request)
    {
        $filter = new SizeFilter();
        $filterItems = $filter->transform($request);

        $langs = Size::where('deleted_by', '=', null)->where($filterItems);

        if ($request->query('createdByUser') == 'true') {
            $langs = $langs->with('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $langs = $langs->with('updatedByUser');
        }


        return new SizeCollection($langs->paginate()->appends($request->query()));
    }

    public function store(StoreSizeRequest $request)
    {
        return new SizeResource(Size::create($request->all()));
    }

    public function show(Request $request, Size $size)
    {
        if ($request->query('createdByUser') == 'true') {
            $size = $size->loadMissing('createdByUser');
        }

        if ($request->query('updatedByUser') == 'true') {
            $size = $size->loadMissing('updatedByUser');
        }

        return new SizeResource($size);
    }

    public function update(UpdateSizeRequest $request, Size $size)
    {
        $size->update($request->all());

        return response()->json([
            'message' => 'Sizes updated successfully'
        ]);
    }

    public function destroy(DestroySizeRequest $request, Size $size)
    {
        $size->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Size deleted successfully',
        ]);
    }
}
