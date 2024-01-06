<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Filters\v1\UserFilter;
use App\Http\Resources\v1\UserCollection;
use App\Http\Resources\v1\UserResource;
use App\Http\Requests\v1\StoreUserRequest;
use App\Http\Requests\v1\UpdateUserRequest;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filter = new UserFilter();
        $filterItems = $filter->transform($request);
        $users = User::where($filterItems);

        if ($request->query('createdBy') == 'true') {
            $users = $users->with('createdBy');
        }

        // dd($users->paginate());

        return new UserCollection($users->paginate()->appends($request->query()));
    }

    public function store(StoreUserRequest $request)
    {
        return new UserResource(User::create($request->all()));
    }

    public function show(Request $request, User $user)
    {
        return new UserResource($user);
    }

    public function update(string $id)
    {
        return 'from update' . $id;
    }

    public function destroy(string $id)
    {
        return 'from destroy' . $id;
    }
}
