<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class LearningModule
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property int $subject_id
 * @property string $code
 * @property int $amount_credit
 * @property string $alias
 * @property int $grade_setting_div
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 *
 * @property Subject $subject
 * @property GradeSetting[]|\Illuminate\Database\Eloquent\Collection $gradeSettings
 */
class LearningModule extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'learning_modules';

    protected $fillable = [
        'school_id',
        'subject_id',
        'code',
        'amount_credit',
        'grade_setting_div',
        'alias',
        'created_by',
        'updated_by',
    ];

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
    public function studyPlans(): HasMany
    {
        return $this->hasMany(StudyPlan::class);
    }

    /**
     * @return HasMany
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * @return HasMany
     */
    public function trainingProgramItems(): HasMany
    {
        return $this->hasMany(TrainingProgramItems::class);
    }

    /**
     * @return HasMany
     */
    public function gradeSettings(): HasMany
    {
        return $this->hasMany(GradeSetting::class);
    }
}
