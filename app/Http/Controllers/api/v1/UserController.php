<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

use App\Filters\v1\UserFilter;

use App\Services\v1\UserService;

use App\Http\Resources\v1\UserResource;
use App\Http\Resources\v1\UserCollection;

use App\Http\Requests\v1\User\ShowUserRequest;
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

        return new UserCollection($users->paginate($request->pageSize)->appends($request->query()));
    }

    public function store(StoreUserRequest $request, UserService $userService)
    {
        $uniquenessChecks = $userService->uniquenessChecks($request);
        if ($uniquenessChecks) {
            return $uniquenessChecks;
        }

        return new UserResource(User::create($request->all()));
    }

    public function show(ShowUserRequest $request, User $user, UserService $userService)
    {
        $existenceCheck = $userService->existenceCheck($user);
        if ($existenceCheck) {
            return $existenceCheck;
        }

        if ($request->query('createdByUser') == 'true') {
            $user = $user->with('createdByUser');
        }
        if ($request->query('updatedByUser') == 'true') {
            $user = $user->with('updatedByUser');
        }

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user, UserService $userService)
    {
        $existenceCheck = $userService->existenceCheck($user);
        if ($existenceCheck) {
            return $existenceCheck;
        }

        $uniquenessChecks = $userService->uniquenessChecks($request, $user);
        if ($uniquenessChecks) {
            return $uniquenessChecks;
        }

        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully',
        ]);
    }

    public function destroy(DestroyUserRequest $request, User $user, UserService $userService)
    {
        $existenceCheck = $userService->existenceCheck($user);
        if ($existenceCheck) {
            return $existenceCheck;
        }
        $deletionAllowanceErrors = $userService->deletionAllowanceCheck($user);
        if ($deletionAllowanceErrors) {
            return $deletionAllowanceErrors;
        }

        $user->tokens()->delete();

        $user->update([
            'deleted_by' => Auth::user()->id,
            'deleted_at' => now(),
        ]);

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
