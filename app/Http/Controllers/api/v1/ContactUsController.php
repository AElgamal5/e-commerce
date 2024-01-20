<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;

use App\Models\ContactUs;

use App\Filters\v1\ContactUsFilter;

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
        $filter = new ContactUsFilter();
        $filterItems = $filter->transform($request);
        $contactUs = ContactUs::where($filterItems);

        return new ContactUsCollection($contactUs->paginate()->appends($request->query()));
    }

    public function store(StoreContactUsRequest $request)
    {
        return new ContactUsResource(ContactUs::create($request->all()));
    }

    public function show(ShowContactUsRequest $request, ContactUs $contactUs)
    {
        return new ContactUsResource($contactUs);
    }

    public function destroy(DestroyContactUsRequest $request, ContactUs $contactUs)
    {
        $contactUs->delete();

        return response()->json([
            'message' => 'ContactUs deleted successfully',
        ]);
    }
}
