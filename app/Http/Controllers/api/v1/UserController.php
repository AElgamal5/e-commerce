<?php

namespace App\Http\Controllers\api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use App\Filters\v1\UserFilter;
use App\Http\Resources\v1\UserResource;
use App\Http\Resources\v1\UserCollection;
use App\Http\Requests\v1\User\IndexUserRequest;
use App\Http\Requests\v1\User\StoreUserRequest;
use App\Http\Requests\v1\User\UpdateUserRequest;
use App\Http\Requests\v1\User\DestroyUserRequest;

class UserController extends Controller
{
    public function index(IndexUserRequest $request)
    {
        $filter = new UserFilter();
        $filterItems = $filter->transform($request);
        $users = User::where('deleted_by', '=', null)->where($filterItems);

        if ($request->query('createdByUser') == 'true') {
            $users = $users->with('createdByUser');
        }
        if ($request->query('updatedByUser') == 'true') {
            $users = $users->with('updatedByUser');
        }

        return new UserCollection($users->paginate()->appends($request->query()));
    }

    public function store(StoreUserRequest $request)
    {
        return new UserResource(User::create($request->all()));
    }

    public function show(Request $request, User $user)
    {
        if ($request->query('createdByUser') == 'true') {
            $user->loadMissing('createdByUser');
        }

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully',
        ]);
    }

    public function destroy(DestroyUserRequest $request, User $user)
    {
        $user->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
