<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductQuantityResource extends JsonResource
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
            "sizeId" => $this->size_id,
            "initialQuantity" => $this->initial_quantity,
            "currentQuantity" => $this->current_quantity,

            'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
            'updatedByUser' => new UserResource($this->whenLoaded('updatedByUser')),
            // 'deletedByUser' => new UserResource($this->whenLoaded('deletedByUser')),
            'color' => new ColorResource($this->whenLoaded('color')),
            'size' => new SizeResource($this->whenLoaded('size')),
        ];
    }
}
