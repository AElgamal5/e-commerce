<?php

namespace App\Services\v1;

use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ColorService
{
    public function uniquenessChecks(Request $request, Color $color = null)
    {
        //code uniqueness check
        if ($request->has('code')) {
            $codeExist = Color::where('deleted_by', null)->where('code', $request->json('code'));

            if (
                ($color && $codeExist->exists() && $color->id != $codeExist->first()->id)
                || (!$color && $codeExist->exists())
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

    public function existenceCheck(Color $color)
    {
        if ($color->exists() && $color->deleted_by != null) {
            return response()->json([
                'message' => 'This color is deleted'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}