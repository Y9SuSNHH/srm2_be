<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Major
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property int $area_id
 * @property string $code
 * @property string $name
 * @property string $shortcode
 * @property int $created_by
 * @property \Carbon\Carbon created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 */
class Major extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'majors';

    protected $fillable = [
        'school_id',
        'area_id',
        'code',
        'name',
        'shortcode',
        'created_by',
        'updated_by',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function getObjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(EnrollmentObject::class,'major_object_maps','major_id','enrollment_object_id');
    }

    /**
     * @return HasMany
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * @return HasMany
     */
    public function trainingPrograms(): HasMany
    {
        return $this->hasMany(Curriculum::class);
    }

    /**
     * @return HasMany
     */
    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
