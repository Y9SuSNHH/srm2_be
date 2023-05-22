<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class Curriculum
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property int $major_id
 * @property \Carbon\Carbon $began_date
 * @property int $created_by
 * @property \Carbon\Carbon created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 */
class Curriculum extends Model
{
    use HasSchool, SoftDeletedTime;

    protected $table = 'training_programs';

    protected $fillable = [
        'school_id',
        'major_id',
        'began_date',
        'created_by',
        'updated_by',
    ];

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getMajor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Major::class,'major_id','id');
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CurriculumItems::class,'training_program_id','id');
    }
}
