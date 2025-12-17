<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'age' => $this->age,
            'gender' => $this->gender,
            'height' => $this->height,
            'weight' => $this->weight,
            'goal' => $this->goal,
            'avatar_path' => $this->avatar_path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
