<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;
use App\Http\Enum\StaffTeam;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Classroom
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property int $major_id
 * @property int $enrollment_object_id
 * @property int $area_id
 * @property int $enrollment_wave_id
 * @property int $proposal
 * @property int|null $object_classification_id
 * @property int|null $staff_id
 * @property string $code
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * relation
 * @property Major $major
 * @property EnrollmentObject $enrollmentObject
 * @property Area $area
 * @property EnrollmentWave $enrollmentWave
 * @property Staff $staff
 * @property Staff $learningManagement
 * @property Student[]|\Illuminate\Database\Eloquent\Collection $students
 */
class Classroom extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'classrooms';

    protected $fillable = [
        'school_id',
        'old_id',
        'major_id',
        'enrollment_object_id',
        'area_id',
        'enrollment_wave_id',
        'proposal',
        'object_classification_id',
        'staff_id',
        'code',
        'description',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'laravel_through_key'
     ];

    /**
     * @return BelongsTo
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * @return BelongsTo
     */
    public function enrollmentObject(): BelongsTo
    {
        return $this->belongsTo(EnrollmentObject::class);
    }

    /**
     * @return BelongsTo
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * @return BelongsTo
     */
    public function enrollmentWave(): BelongsTo
    {
        return $this->belongsTo(EnrollmentWave::class);
    }

    /**
     * @return BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return BelongsTo
     */
    public function learningManagement(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id')->where('team', StaffTeam::LEARNING_MANAGEMENT);
    }

    /**
     * @return BelongsToMany
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_classrooms');
    }

    /**
     * @return HasMany
     */
    public function studentClassrooms(): HasMany
    {
        return $this->hasMany(StudentClassroom::class);
    }

    /**
     * @return HasMany
     */
    public function studyPlans(): HasMany
    {
        return $this->hasMany(StudyPlan::class);
    }

    public function classroomReceivable() : HasMany
    {
        return $this->hasMany(ClassroomReceivable::class);
    }

    public function period() : hasMany
    {
        return $this->hasMany(Period::class);
    }
}
