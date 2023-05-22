<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IgnoreLearningModule extends Model
{
    protected $fillable = [
        'id',
        'created_by',
        'student_id',
        'learning_module_id',
        'reason',
        'storage_file_id',
    ];

    /**
     * @return BelongsTo
     */
    public function learningModule(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class);
    }


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
    public function storageFile(): BelongsTo
    {
        return $this->belongsTo(StorageFile::class);
    }
}