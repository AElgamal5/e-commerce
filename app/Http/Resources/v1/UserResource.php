<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "email" => $this->email,
            "role" => $this->role,
            "phone" => $this->phone,
            "countryCode" => $this->country_code,
            // "createdBy" => $this->created_by,
            // "updatedBy" => $this->updated_by,
            // "deletedBy" => $this->deleted_by,
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
