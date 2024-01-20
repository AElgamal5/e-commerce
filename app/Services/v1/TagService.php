<?php

namespace App\Services\v1;

use App\Models\Tag;
use Illuminate\Http\Response;

class TagService
{
    public function existenceCheck(Tag $tag)
    {
        if ($tag->exists() && $tag->deleted_by != null) {
            return response()->json([
                'message' => 'This tag is deleted'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}