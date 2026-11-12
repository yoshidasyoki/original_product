<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'title', 'position'])]
class Curriculum extends Model
{
    protected $table = 'curriculums';
}
