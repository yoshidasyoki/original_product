<?php

namespace App\Http\Resources\Me;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->curriculum->id,
            'title' => $this->curriculum->title,
            'status' => $this->status,
        ];
    }
}
