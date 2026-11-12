<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'curriculum_id', 'status'])]
class UserLearningProgress extends Model
{
    //
}
