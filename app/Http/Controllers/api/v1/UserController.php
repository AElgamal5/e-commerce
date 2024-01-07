<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Filters\v1\UserFilter;
use App\Http\Resources\v1\UserCollection;
use App\Http\Resources\v1\UserResource;
use App\Http\Requests\v1\User\StoreUserRequest;
use App\Http\Requests\v1\User\UpdateUserRequest;
use App\Http\Requests\v1\User\DestroyUserRequest;
use App\Http\Requests\v1\User\IndexUserRequest;

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
        //created_by check
        $createdBy = $request->json('createdBy');
        if ($createdBy) {
            $createdByUser = User::where('id', $createdBy)->first();

            if ($createdByUser->deleted_By || $createdByUser->deleted_at) {
                return response()->json([
                    'errors' => [
                        'createdBy' => [
                            'message' => 'This user has been deleted'
                        ]
                    ]
                ], 400);
            }
        }

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
        //updated_By check
        $updatedBy = $request->json('updatedBy');
        if ($updatedBy) {

            $updatedByUser = User::where('id', $updatedBy)->first();

            if ($updatedByUser->deleted_By || $updatedByUser->deleted_at) {

                return response()->json([
                    'errors' => [
                        'updatedBy' => [
                            'message' => 'This user has been deleted'
                        ]
                    ]
                ], 400);
            }

        }


        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully',
        ]);
    }

    public function destroy(DestroyUserRequest $request, User $user)
    {

        //deleted_By check
        $deletedBy = $request->json('deletedBy');
        if ($deletedBy) {

            $deletedByUser = User::where('id', $deletedBy)->first();

            if ($deletedByUser->deleted_By || $deletedByUser->deleted_at) {

                return response()->json([
                    'errors' => [
                        'deletedBy' => [
                            'message' => 'This user has been deleted'
                        ]
                    ]
                ], 400);
            }

        }


        $user->update([
            'deleted_by' => $request->json('deletedBy'),
            'deleted_at' => now(),
        ]);

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
