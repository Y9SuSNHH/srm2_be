<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class CurriculumItems
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $training_program_id
 * @property int $learning_module_id
 * @property int $enrollment_object_id
 * @property int $subject_id
 * @property int $created_by
 * @property \Carbon\Carbon created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 */
class CurriculumItems extends Model
{
    use SoftDeletedTime;

    protected $table = 'training_program_items';

    protected $fillable = [
        'training_program_id',
        'learning_module_id',
        'enrollment_object_id',
        'subject_id',
        'created_by',
        'updated_by'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getObjects(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EnrollmentObject::class,'id','enrollment_object_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getLearningModule(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LearningModule::class,'learning_module_id','id');
    }
}
