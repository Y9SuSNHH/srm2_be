<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Class Handover
 * @package App\Eloquent
 *
 * @property int $id
 * @property $code
 * @property $staff_id
 * @property $handover_date
 * @property $return_student_code_status
 * @property $no
 * @property $decision_date
 * @property $first_day_of_school
 * @property $area_id
 * @property $status
 * @property $student_status
 * @property $profile_status
 * @property $is_lock
 * @property $created_at
 * @property $created_by
 * @property $updated_at
 * @property $updated_by
 * @property $deleted_time
 * @property $deleted_by
 */
class Handover extends Model
{
    use SoftDeletedTime;

    protected $table = 'handovers';

    protected $fillable = [
        'id',
        'code',
        'staff_id',
        'handover_date',
        'return_student_code_status',
        'no',
        'decision_date',
        'first_day_of_school',
        'area_id',
        'status',
        'student_status',
        'profile_status',
        'is_lock',
        'created_by',
        'updated_by',
        'deleted_time',
        'deleted_by',
    ];

    /**
     * @return HasMany
     */
    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    /**
     * @return HasManyThrough
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(Student::class, StudentProfile::class);
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
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}