<?php

namespace App\Http\Controllers\api\v1;

use App\Models\ContactUs;
use App\Filters\v1\ContactUsFilter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Http\Resources\v1\ContactUsResource;
use App\Http\Resources\v1\ContactUsCollection;
use App\Http\Requests\v1\ContactUs\ShowContactUsRequest;
use App\Http\Requests\v1\ContactUs\IndexContactUsRequest;
use App\Http\Requests\v1\ContactUs\StoreContactUsRequest;
use App\Http\Requests\v1\ContactUs\DestroyContactUsRequest;


class ContactUsController extends Controller
{
    public function index(IndexContactUsRequest $request)
    {

        $key = $this->generateCacheKey('contact_us', $request->page ?? 1, $request->PageSize ?? 15, $request->all());

        if (Redis::exists($key)) {
            $contactUs = Redis::get($key);
            return unserialize($contactUs);
        }

        $filter = new ContactUsFilter();
        $filterItems = $filter->transform($request);
        $contactUs = ContactUs::search($request->search)->where($filterItems);

        if ($request->pageSize == -1) {
            $contactUs = new ContactUsCollection($contactUs->get());
        } else {
            $contactUs = new ContactUsCollection($contactUs->paginate($request->pageSize)->appends($request->query()));
        }

        Redis::set($key, serialize($contactUs));

        return $contactUs;

    }

    public function store(StoreContactUsRequest $request)
    {
        return new ContactUsResource(ContactUs::create($request->all()));
    }

    public function show(ShowContactUsRequest $request, ContactUs $contactUs)
    {
        $key = $this->generateCacheKeyForOne('contact_us', $contactUs->id, $request->all());

        if (Redis::exists($key)) {
            $result = Redis::get($key);
            return unserialize($result);
        }

        $result = new ContactUsResource($contactUs);

        Redis::set($key, serialize($result));

        return $result;
    }

    public function destroy(DestroyContactUsRequest $request, ContactUs $contactUs)
    {
        $contactUs->delete();

        return response()->json([
            'message' => 'ContactUs deleted successfully',
        ]);
    }
}
