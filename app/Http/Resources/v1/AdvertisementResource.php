<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisementResource extends JsonResource
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
            "name" => $this->name,
            "image" => $this->image ? url('storage/' . $this->image) : null,
            "link" => $this->link,
            "status" => $this->status,
            "createdBy" => $this->created_by,
            "updatedBy" => $this->updated_by,
            'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
            'updatedByUser' => new UserResource($this->whenLoaded('updatedByUser')),
            // 'deletedByUser' => new UserResource($this->whenLoaded('deletedByUser')),
        ];
    }
}
