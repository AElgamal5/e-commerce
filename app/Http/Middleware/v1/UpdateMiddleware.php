<?php

namespace App\Http\Middleware\v1;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\User;

class UpdateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $updatedBy = $request->json('updatedBy');

        if (!$updatedBy) {
            return response()->json([
                'errors' => [
                    'updatedBy' => [
                        'This field is required'
                    ]
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $updatedByUser = User::where('id', $updatedBy)->first();

        if (!$updatedByUser) {
            return response()->json([
                'errors' => [
                    'updatedBy' => [
                        'This user does not exist'
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        if ($updatedByUser->deleted_By || $updatedByUser->deleted_at) {
            return response()->json([
                'errors' => [
                    'updatedBy' => [
                        'This user has been deleted'
                    ]
                ]
            ], Response::HTTP_CONFLICT);
        }

        return $next($request);
    }
}
