<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class StudentClassroom
 * @package App\Eloquent
 *
 * @property $id
 * @property $student_id
 * @property $classroom_id
 * @property \Carbon\Carbon $began_date
 * @property \Carbon\Carbon $began_at
 * @property \Carbon\Carbon $ended_at
 * @property $reference_type
 * @property $reference_id
 * @property $created_by
 * @property $updated_by
 */
class StudentClassroom extends Model
{
    protected $table = 'student_classrooms';

    protected $fillable = [
        'student_id',
        'classroom_id',
        'began_date',
        'began_at',
        'ended_at',
        'reference_type',
        'reference_id',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['began_date', 'began_at', 'ended_at'];

    public function classroom(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function studyPlans(): HasMany
    {
        return $this->hasMany(StudyPlan::class, 'classroom_id', 'classroom_id');
    }

    /**
     * @return HasMany
     */
    public function learningProcess(): HasMany
    {
        return $this->hasMany(LearningProcess::class, 'student_id', 'student_id');
    }

    public function getClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id', 'id');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class, 'classroom_id', 'classroom_id');
    }
}