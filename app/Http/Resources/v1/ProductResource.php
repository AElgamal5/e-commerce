<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            "status" => $this->status,
            "year" => $this->year,
            "price" => $this->price,
            "discountType" => $this->discount_type,
            "discountValue" => $this->discount_value,
            "initialQuantity" => $this->initial_quantity,
            "currentQuantity" => $this->current_quantity,
            "categoryID" => $this->category_id,

            "createdBy" => $this->created_by,
            "updatedBy" => $this->updated_by,

            'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
            'updatedByUser' => new UserResource($this->whenLoaded('updatedByUser')),
            // 'deletedByUser' => new UserResource($this->whenLoaded('deletedByUser')),
            'translations' => new ProductTranslationsCollection($this->whenLoaded('translations')),
            'tags' => new ProductTagCollection($this->whenLoaded('tags')),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
