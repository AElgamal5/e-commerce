<?php

namespace App\Http\Middleware\v1;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\User;

class StoreMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $createdBy = $request->json('createdBy');

        if (!$createdBy) {
            return response()->json([
                'errors' => [
                    'createdBy' => [
                        'This field is required'
                    ]
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $createdByUser = User::where('id', $createdBy)->first();

        if (!$createdByUser) {
            return response()->json([
                'errors' => [
                    'createdBy' => [
                        'This user does not exist'
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        if ($createdByUser->deleted_by || $createdByUser->deleted_at) {
            return response()->json([
                'errors' => [
                    'createdBy' => [
                        'This user is deleted'
                    ]
                ]
            ], Response::HTTP_CONFLICT);
        }

        return $next($request);
    }
}
