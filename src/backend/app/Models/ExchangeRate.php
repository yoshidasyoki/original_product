<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['base_currency', 'quote_currency', 'rate', 'record_date'])]
class ExchangeRate extends Model
{
    //
}
