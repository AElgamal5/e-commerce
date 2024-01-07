<?php

namespace App\Http\Controllers\api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Auth\LoginRequest;
use App\Http\Requests\v1\Auth\SignupRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', '=', $request->json('email'))->first();

        if ($user->deleted_by || $user->deleted_at) {
            return response()->json([
                'message' => 'Your user has been deleted.',
            ], 400);
        }

        if (Auth::attempt(['email' => $request->json('email'), 'password' => $request->json('password')])) {

            $authUser = Auth::user();

            if ($authUser->role == 0) {
                $adminToken = $user->createToken('admin-token', ['*'], now()->addDays(5))->plainTextToken;
                return response()->json([
                    'message' => 'You have logged in successfully',
                    'role' => 'admin',
                    'token' => $adminToken,
                ]);
            } elseif ($authUser->role == 1) {
                $employeeToken = $user->createToken('employee-token', ['employee'], now()->addMinutes(5))->plainTextToken;
                return response()->json([
                    'message' => 'You have logged in successfully',
                    'role' => 'employee',
                    'token' => $employeeToken,
                ]);
            } elseif ($authUser->role == 2) {
                $customerToken = $user->createToken('customer-token', ['customer'], now()->addMinutes(5))->plainTextToken;
                return response()->json([
                    'message' => 'You have logged in successfully',
                    'role' => 'customer',
                    'token' => $customerToken,
                ]);
            } else {
                return response()->json([
                    'message' => 'You are not authorized to access this resource.',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        return response()->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
    }

    public function signup(SignupRequest $request)
    {
        User::create($request->all());

        return response()->json([
            'message' => "You have signed-up successfully"
        ], Response::HTTP_CREATED);
    }
}
