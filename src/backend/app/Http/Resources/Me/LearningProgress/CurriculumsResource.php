<?php

namespace App\Http\Resources\Me\LearningProgress;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumsResource extends JsonResource
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
