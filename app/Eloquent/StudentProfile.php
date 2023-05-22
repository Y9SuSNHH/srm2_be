<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class StudentProfile
 * @package App\Eloquent
 *
 * @property string $documents
 *
 * @property Profile $profile
 * @property int $handover_id
 * @property Handover $handover
 */
class StudentProfile extends Model
{
    use SoftDeletedTime, HasSchool;
    protected $table = 'student_profiles';

    protected $fillable = [
        'id',
        'school_id',
        'profile_id',
        'profile_code',
        'staff_id',
        'is_ts8',
        'area_id',
        'major_id',
        'enrollment_object_id',
        'enrollment_wave_id',
        'classroom_id',
        'level',
        'documents',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'handover_id',
    ];

    /**
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * @return HasOne
     */
    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'id', 'staff_id');
    }

    public function receivable() : HasMany
    {
        return $this->hasMany(StudentReceivable::class);
    }

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
    public function getAdmissionsCounselor(): BelongsTo
    {
        return $this->belongsTo(Staff::class,'staff_id','id');
    }
    public function studentReceivables(): HasMany
    {
        return $this->hasMany(StudentReceivable::class, 'student_profile_id', 'id');
    }

    public function getProfile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id');
    }

    public function getTvts() :BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return HasOne
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }
}