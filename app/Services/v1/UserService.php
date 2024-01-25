<?php

namespace App\Services\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function uniquenessChecks(Request $request, User $user = null)
    {
        //email uniqueness check
        if ($request->has('email')) {
            $emailExist = User::where('deleted_by', null)->where('email', $request->email);

            if (
                ($user && $emailExist->exists() && $user->id != $emailExist->first()->id)
                || (!$user && $emailExist->exists())
            ) {
                return response()->json([
                    'errors' => [
                        'email' => [
                            'Email must be unique'
                        ]
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        //phone & country code uniqueness check
        if ($request->has('phone') || $request->has('countryCode')) {

            if (!$request->has('phone')) {
                return response()->json([
                    'errors' => [
                        'phone' => [
                            'Phone is required'
                        ]
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if (!$request->has('countryCode')) {
                return response()->json([
                    'errors' => [
                        'countryCode' => [
                            'Country code is required'
                        ]
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $phoneExist = User::where('deleted_by', null)
                ->where('phone', $request->phone)
                ->where('country_code', $request->countryCode);

            if (
                ($user && $phoneExist->exists() && $user->id != $phoneExist->first()->id)
                || (!$user && $phoneExist->exists())
            ) {
                return response()->json([
                    'errors' => [
                        'phone' => [
                            'Phone must be unique in each country code'
                        ]
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
    }

    public function existenceCheck(User $user)
    {
        if ($user->exists() && $user->deleted_by != null) {
            return response()->json([
                'message' => 'This user is deleted'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deletionAllowanceCheck(User $user)
    {
        if ($user->role < Auth::user()->role) {
            return response()->json([
                'message' => 'Can not delete user with higher role than you'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}