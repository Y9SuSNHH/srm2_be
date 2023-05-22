<?php

namespace App\Eloquent;

use App\Http\Enum\ReceivablePurpose;
use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Http\Enum\TuitionFee;
use App\Eloquent\Traits\HasSchool;

/**
 * Class Student
 * @package App\Eloquent
 *
 * @property
 *
 * @property int $id
 * @property int $school_id
 * @property int $student_profile_id
 * @property string $student_code
 * @property string $profile_code
 * @property string $account
 * @property string $email
 * @property int $profile_status
 * @property int $student_status
 *
 * @property StudentProfile $studentProfile
 * @property Profile $profile
 * @property Grade[]|\Illuminate\Database\Eloquent\Collection $grades
 * @property Classroom[]|\Illuminate\Database\Eloquent\Collection $classrooms
 * @property Classroom[]|\Illuminate\Database\Eloquent\Collection $oldestClassrooms
 */
class Student extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'students';

    protected $fillable = [
        'id',
        'school_id',
        'student_profile_id',
        'student_code',
        'account',
        'email',
        'profile_status',
        'student_status',
        'note',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    /**
     * @return BelongsToMany
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, StudentClassroom::class)->wherePivotNull('ended_at');
    }


    /**
     * @return HasOneThrough
     */
    public function classroom(): HasOneThrough
    {
        return $this->hasOneThrough(
            Classroom::class,
            StudentClassroom::class,
            'student_id',
            'id',
            'id',
            'classroom_id')->whereNull('student_classrooms.ended_at');
    }

    /**
     * @return BelongsTo
     */
    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function learningEngagement(): HasOne
    {
        return $this->hasOne(LearningEngagementProcess::class);
    }

    public function getProfile(): HasOneThrough
    {
        return $this->hasOneThrough(Profile::class, StudentProfile::class, 'id', 'id', 'student_profile_id', 'profile_id');
    }

    public function getStudentReceivables(): HasManyThrough
    {
        return $this->hasManyThrough(StudentReceivable::class, StudentProfile::class, 'id', 'student_profile_id')
            ->where('purpose', ReceivablePurpose::TUITION_FEE);
    }

    /**
     * @return HasMany
     */
    public function studentClassrooms(): HasMany
    {
        return $this->hasMany(StudentClassroom::class);
    }

    /**
     * @return HasOneThrough
     */
    public function staff(): HasOneThrough
    {
        return $this->hasOneThrough(
            Staff::class, //Bảng cần lấy
            StudentProfile::class, // Bảng pivot
            'id', // id Bảng pivot
            'id', // id Bảng cần lấy
            'id',
        // id Bảng hiện tại
        );
    }

    /**
     * @return HasOneThrough
     */
    public function profile(): HasOneThrough
    {
        return $this->hasOneThrough(
            Profile::class, //Bảng cần lấy
            StudentProfile::class, // Bảng pivot
            'id', // id Bảng pivot
            'id', // id Bảng cần lấy
            'student_profile_id', // id Bảng hiện tại
            'profile_id',
        // Id bảng cần lấy ở bảng pivot
        );
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
    public function revisionHistories() : HasMany
    {
        return $this->hasMany(StudentRevisionHistory::class);
    }

    /**
     * @return BelongsTo
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return HasMany
     */
    public function petitions(): HasMany
    {
        return $this->hasMany(Petition::class);
    }

    /**
     * @return HasMany
     */
    public function careHistories(): HasMany
    {
        return $this->hasMany(CareHistory::class);
    }

    /**
     * @return HasMany
     */
    public function ignoreLearningModules(): HasMany
    {
        return $this->hasMany(IgnoreLearningModule::class);
    }

    /**
     * @return HasMany
     */
    public function financialCredits(): HasMany
    {
        return $this->hasMany(FinancialCredit::class, 'student_profile_id', 'student_profile_id');
    }

    
    /**
     * @return HasOneThrough
     */
    public function getTvts(): HasOneThrough
    {
        return $this->hasOneThrough(Staff::class,StudentProfile::class, 'id', 'id','student_profile_id','staff_id');
    }

    /**
     * @return BelongsTo
     */
    public function getStudentRevenues(): BelongsTo
    {
        return $this->BelongsTo(StudentRevenue::class,'student_profile_id','student_profile_id')
                    ->where('purpose', ReceivablePurpose::TUITION_FEE);

    }

    /**
     * @return HasOne
     */
    public function latestStudentRevisionHistory(): HasOne
    {
        return $this->hasOne(StudentRevisionHistory::class)->latestOfMany('ended_at');
    }
}