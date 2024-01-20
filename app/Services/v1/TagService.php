<?php

namespace App\Services\v1;

use App\Models\Tag;
use Illuminate\Http\Request;
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

    public function existenceByIdCheck(int $tagId)
    {
        $tag = Tag::find($tagId);
        $response = $this->existenceCheck($tag);

        if ($response) {
            $content = [
                'message' => "Tag with id: $tagId is deleted"
            ];
            $response->setContent(json_encode($content));
            return $response;
        }
    }

    public function productTagsCheck(Request $request)
    {
        if (!$request->has('tags')) {
            return;
        }

        $input = $request->all();

        foreach ($input['tags'] as $tag) {
            $existenceByIdErrors = $this->existenceByIdCheck($tag);
            if ($existenceByIdErrors) {
                return $existenceByIdErrors;
            }
        }
    }
}