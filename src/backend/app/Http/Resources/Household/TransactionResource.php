<?php

namespace App\Http\Resources\Household;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'date' => $this->record_date,
            'type' => $this->householdCategory->type,
            'category' => $this->householdCategory->only(['id', 'name', 'icon']),
            'memo' => $this->memo,
            'amount' => $this->amount,
        ];
    }
}
