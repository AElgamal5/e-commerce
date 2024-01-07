<?php

namespace App\Http\Middleware\v1;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\User;

class DestroyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deletedBy = $request->json('deletedBy');

        if (!$deletedBy) {
            return response()->json([
                'errors' => [
                    'deletedBy' => [
                        'This field is required'
                    ]
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $deletedByUser = User::where('id', $deletedBy)->first();

        if (!$deletedByUser) {
            return response()->json([
                'errors' => [
                    'deletedBy' => [
                        'This user does not exist'
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        if ($deletedByUser->deleted_By || $deletedByUser->deleted_at) {
            return response()->json([
                'errors' => [
                    'deletedBy' => [
                        'This user has been deleted'
                    ]
                ]
            ], Response::HTTP_CONFLICT);
        }

        return $next($request);
    }
}
