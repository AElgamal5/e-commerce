<?php

namespace App\Http\Controllers\api\v1;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\Models\User;

use App\Services\v1\UserService;

use App\Http\Requests\v1\Auth\LoginRequest;
use App\Http\Requests\v1\Auth\SignupRequest;

use App\Http\Resources\v1\UserResource;

class AuthController extends Controller
{
    public function login(LoginRequest $request, UserService $userService)
    {
        $user = User::where('email', '=', $request->email)->first();

        $existenceCheck = $userService->existenceCheck($user);
        if ($existenceCheck) {
            return $existenceCheck;
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            $authUser = Auth::user();

            if ($authUser->role == 0) {
                $adminToken = $user->createToken('admin-token', ['*'], now()->addDays(1))->plainTextToken;
                return response()->json([
                    'message' => 'You have logged in successfully',
                    'role' => 'admin',
                    'token' => $adminToken,
                    'user' => new UserResource($user),
                ]);
            } elseif ($authUser->role == 1) {
                $employeeToken = $user->createToken('employee-token', ['employee'], now()->addDays(1))->plainTextToken;
                return response()->json([
                    'message' => 'You have logged in successfully',
                    'role' => 'employee',
                    'token' => $employeeToken,
                    'user' => new UserResource($user),
                ]);
            } elseif ($authUser->role == 2) {
                $customerToken = $user->createToken('customer-token', ['customer'], now()->addMinutes(60))->plainTextToken;
                return response()->json([
                    'message' => 'You have logged in successfully',
                    'role' => 'customer',
                    'token' => $customerToken,
                    'user' => new UserResource($user),
                ]);
            } else {
                return response()->json([
                    'message' => 'You are not authorized to access this resource.',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        return response()->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
    }

    public function signup(SignupRequest $request, UserService $userService)
    {

        $uniquenessChecks = $userService->uniquenessChecks($request);
        if ($uniquenessChecks) {
            return $uniquenessChecks;
        }

        User::create($request->all());

        return response()->json([
            'message' => "You have signed-up successfully"
        ], Response::HTTP_CREATED);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
