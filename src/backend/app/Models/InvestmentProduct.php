<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'currency'])]
class InvestmentProduct extends Model
{
    public function marketPrice()
    {
        return $this->hasMany(MarketPrice::class);
    }
}
