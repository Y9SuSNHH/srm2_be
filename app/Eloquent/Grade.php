<?php

namespace App\Eloquent;

use App\Eloquent\Traits\Joinable;
use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Grade
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $learning_module_id
 * @property int $student_id
 * @property \Carbon\Carbon $exam_date
 * @property int $grade_setting_id
 * @property float $value
 * @property string $note
 * @property int $storage_file_id
 *
 * @property LearningModule $learningModule
 * @property GradeSetting $gradeSetting
 * @property Student $student
 * @property GradeValue[]|\Illuminate\Database\Eloquent\Collection $gradeValues
 */
class Grade extends Model
{
    use SoftDeletedTime, Joinable;

    protected $fillable = [
        'learning_module_id',
        'student_id',
        'exam_date',
//        'grade_setting_id',
//        'value',
        'note',
        'storage_file_id',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['exam_date'];

    /**
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo
     */
    public function learningModule(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class);
    }

    /**
     * @return HasMany
     */
    public function gradeValues(): HasMany
    {
        return $this->hasMany(GradeValue::class);
    }

    /**
     * @return BelongsTo
     */
    public function storageFile(): BelongsTo
    {
        return $this->belongsTo(StorageFile::class);
    }
}
