<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;
use App\Http\Domain\LearningModule\Models\LearningModule\LearningModule;

/**
 * Class Subject
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 */
class Subject extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'subjects';

    protected $fillable = [
        'school_id',
        'code',
        'name',
        'description',
        'created_by',
        'updated_by',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getLearningModules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LearningModule::class,'subject_id','id');
    }
}
