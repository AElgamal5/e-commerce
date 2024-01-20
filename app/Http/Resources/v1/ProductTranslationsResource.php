<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductTranslationsResource extends JsonResource
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
            "productId" => $this->product_id,
            "languageId" => $this->language_id,
            "name" => $this->name,
            "description" => $this->description,
            "createdBy" => $this->created_by,
            "updatedBy" => $this->updated_by,
            'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
            'updatedByUser' => new UserResource($this->whenLoaded('updatedByUser')),
            // 'deletedByUser' => new UserResource($this->whenLoaded('deletedByUser')),
            'language' => new LanguageResource($this->whenLoaded('language')),
        ];
    }
}
