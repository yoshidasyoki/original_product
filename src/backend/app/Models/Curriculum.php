<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'title', 'position'])]
class Curriculum extends Model
{
    protected $table = 'curriculums';

    public function learningProgress(): HasMany
    {
        return $this->hasMany(UserLearningProgress::class);
    }
}
