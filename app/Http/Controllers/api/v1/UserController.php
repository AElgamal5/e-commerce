<?php

namespace App\Http\Controllers\api\v1;

use App\Models\User;

use App\Filters\v1\UserFilter;

use App\Services\v1\UserService;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Redis;
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
        $key = $this->generateCacheKey('users', $request->page ?? 1, $request->PageSize ?? 15, $request->all());

        if (Redis::exists($key)) {
            $users = Redis::get($key);
            return unserialize($users);
        }


        $filter = new UserFilter();
        $filterItems = $filter->transform($request);
        $users = User::search($request->search)->where('deleted_by', '=', null)->where($filterItems);

        if ($request->query('createdByUser') == 'true') {
            $users = $users->with('createdByUser');
        }
        if ($request->query('updatedByUser') == 'true') {
            $users = $users->with('updatedByUser');
        }

        if ($request->pageSize == -1) {
            $users = new UserCollection($users->get());
        } else {
            $users = new UserCollection($users->paginate($request->pageSize)->appends($request->query()));
        }
        Redis::set($key, serialize($users));

        return $users;
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

        $key = $this->generateCacheKeyForOne('users', $user->id, $request->all());

        if (Redis::exists($key)) {
            $result = Redis::get($key);
            return unserialize($result);
        }

        if ($request->query('createdByUser') == 'true') {
            $user = $user->with('createdByUser');
        }
        if ($request->query('updatedByUser') == 'true') {
            $user = $user->with('updatedByUser');
        }

        $result = new UserResource($user);

        Redis::set($key, serialize($result));

        return $result;
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

        $user->update($request->all());

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
