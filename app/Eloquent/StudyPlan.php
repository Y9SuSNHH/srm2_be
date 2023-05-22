<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * Class Study Plan
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $classroom_id
 * @property int $semester
 * @property int $slot
 * @property int $learning_module_id
 * @property int $subject_id
 * @property \Carbon\Carbon $study_began_date
 * @property \Carbon\Carbon $study_ended_date
 * @property \Carbon\Carbon $day_of_the_test
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 *
 * @property LearningModule $learningModule
 * @property Classroom $classroom
 */
class StudyPlan extends Model
{
    use SoftDeletedTime;

    protected $table = 'study_plans';

    protected $fillable = [
        'classroom_id',
        'semester',
        'slot',
        'learning_module_id',
        'subject_id',
        'study_began_date',
        'study_ended_date',
        'day_of_the_test',
        'created_by',
        'updated_by',
    ];

    protected $dates = [
        'study_began_date',
        'study_ended_date',
        'day_of_the_test',
    ];

    /**
     * @return BelongsTo
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * @return BelongsTo
     */
    public function learningModule(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class);
    }

    /**
     * @return BelongsTo
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return HasMany
     */
    public function learningProcess(): HasMany
    {
        return $this->hasMany(LearningProcess::class, 'learning_modules_id', 'learning_module_id');
    }
}
