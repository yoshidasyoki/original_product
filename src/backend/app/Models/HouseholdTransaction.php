<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'household_category_id', 'record_date', 'memo', 'amount'])]
class HouseholdTransaction extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function householdCategory()
    {
        return $this->belongsTo(HouseholdCategory::class);
    }
}
