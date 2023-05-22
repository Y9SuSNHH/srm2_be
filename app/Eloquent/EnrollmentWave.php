<?php


namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EnrollmentWave
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property int $area_id
 * @property \Carbon\Carbon $first_day_of_school
 * @property \Carbon\Carbon $enrollment_start_date
 * @property \Carbon\Carbon $enrollment_end_date
 * @property \Carbon\Carbon $application_submission_deadline
 * @property \Carbon\Carbon $tuition_payment_deadline
 * @property int $school_year
 * @property int $locked
 * @property int $created_by
 * @property \Carbon\Carbon created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 * @property int $classrooms_count
 * @property int $student_profiles_count
 *
 *  * relation
 * @property Area $area
 * @property Classroom[]|\Illuminate\Database\Eloquent\Collection $classrooms
 * @property StudentProfile[]|\Illuminate\Database\Eloquent\Collection $studentProfiles
 */
class EnrollmentWave extends Model
{
    use HasSchool, SoftDeletedTime;

    protected $table = 'enrollment_waves';

    protected $fillable = [
        'school_id',
        'area_id',
        'first_day_of_school',
        'group_number',
        'enrollment_start_date',
        'enrollment_end_date',
        'application_submission_deadline',
        'tuition_payment_deadline',
        'locked',
        'created_by',
        'updated_by',
        'school_year',
    ];

    protected $dates = [
        'first_day_of_school',
        'enrollment_start_date',
        'enrollment_end_date',
        'application_submission_deadline',
        'tuition_payment_deadline',
    ];
     /**
     * @return BelongsTo
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }
   
    /**
     * @return BelongsTo
     */
    public function creditPrice(): BelongsTo
    {
        return $this->belongsTo(CreditPrice::class, 'school_id', 'school_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classrooms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function studentProfiles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }
}
