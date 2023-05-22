<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeSetting extends Model
{
    use SoftDeletedTime;

    protected $fillable = [
        'learning_module_id',
        'grade_div',
        'priority',
        'created_by',
        'updated_by',
    ];

    /**
     * @return HasMany
     */
    public function gradeValues(): HasMany
    {
        return $this->hasMany(GradeValue::class);
    }
}