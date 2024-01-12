<?php

namespace App\Services\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserService
{
    public function uniquenessChecks(Request $request, User $user = null)
    {
        //email uniqueness check
        if ($request->has('email')) {
            $emailExist = User::where('deleted_by', null)->where('email', $request->json('email'));

            if (
                ($user->exists() && $emailExist->exists() && $user->id != $emailExist->first()->id)
                || (!$user->exists() && $emailExist->exists())
            ) {
                return response()->json([
                    'errors' => [
                        'email' => [
                            'Email must be unique'
                        ]
                    ]
                ], Response::HTTP_BAD_REQUEST);
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
                ], Response::HTTP_BAD_REQUEST);
            }
            if (!$request->has('countryCode')) {
                return response()->json([
                    'errors' => [
                        'countryCode' => [
                            'Country code is required'
                        ]
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            $phoneExist = User::where('deleted_by', null)
                ->where('phone', $request->json('phone'))
                ->where('country_code', $request->json('countryCode'));

            if (
                ($user->exists() && $phoneExist->exists() && $user->id != $phoneExist->first()->id)
                || (!$user->exists() && $phoneExist->exists())
            ) {
                return response()->json([
                    'errors' => [
                        'phone' => [
                            'Phone must be unique in each country code'
                        ]
                    ]
                ], Response::HTTP_BAD_REQUEST);
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
}