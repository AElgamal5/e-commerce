<?php

namespace App\Services\v1;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LanguageService
{
    public function uniquenessChecks(Request $request, Language $language = null)
    {
        //code uniqueness check
        if ($request->has('code')) {
            $codeExist = Language::where('deleted_by', null)->where('code', $request->json('code'));

            if (
                ($language && $codeExist->exists() && $language->id != $codeExist->first()->id)
                || (!$language && $codeExist->exists())
            ) {
                return response()->json([
                    'errors' => [
                        'code' => [
                            'Code must be unique'
                        ]
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        //name uniqueness check
        if ($request->has('name')) {
            $nameExist = Language::where('deleted_by', null)->where('name', $request->json('name'));

            if (
                ($language && $nameExist->exists() && $language->id != $nameExist->first()->id)
                || (!$language && $nameExist->exists())
            ) {
                return response()->json([
                    'errors' => [
                        'name' => [
                            'Name must be unique'
                        ]
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    public function existenceCheck(Language $language)
    {
        if ($language->exists() && $language->deleted_by != null) {
            return response()->json([
                'message' => 'This language is deleted'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}