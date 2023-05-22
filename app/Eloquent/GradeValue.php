<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class GradeValue
 * @package App\Eloquent
 *
 * @property $id
 * @property $grade_div
 * @property $grade_id
 * @property $value
 *
 * @property GradeSetting $gradeSetting
 */
class GradeValue extends Model
{
    protected $table = 'grade_values';

    protected $fillable = [
        'grade_div',
        'grade_id',
        'value',
    ];

    /**
     * @return BelongsTo
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}