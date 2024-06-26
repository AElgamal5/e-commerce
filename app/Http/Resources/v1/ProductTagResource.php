<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductTagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            "id" => $this->id,
            // "productId" => $this->product_id,
            // "tagId" => $this->tag_id,
            "createdBy" => $this->created_by,
            'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
            'tag' => new TagResource($this->whenLoaded('tag')),
        ];
        // return new TagResource($this->whenLoaded('tag'));
    }
}
