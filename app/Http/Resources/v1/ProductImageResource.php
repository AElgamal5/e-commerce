<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "colorId" => $this->color_id,
            "image" => url('storage/' . $this->image),
            "createdBy" => $this->created_by,

            'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
            'color' => new ColorResource($this->whenLoaded('color')),
        ];
    }
}
