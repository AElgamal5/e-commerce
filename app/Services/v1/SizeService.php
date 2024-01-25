<?php

namespace App\Services\v1;

use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SizeService
{
    public function uniquenessChecks(Request $request, Size $size = null)
    {
        //code uniqueness check
        if ($request->has('code')) {
            $codeExist = Size::where('deleted_by', null)->where('code', $request->json('code'));

            if (
                ($size && $codeExist->exists() && $size->id != $codeExist->first()->id)
                || (!$size && $codeExist->exists())
            ) {
                return response()->json([
                    'errors' => [
                        'code' => [
                            'Code must be unique'
                        ]
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
    }

    public function existenceCheck(Size $size)
    {
        if ($size->exists() && $size->deleted_by != null) {
            return response()->json([
                'message' => 'This size is deleted'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}