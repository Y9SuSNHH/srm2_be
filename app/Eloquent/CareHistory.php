<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Care History
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $student_id
 * @property string $content
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 */
class CareHistory extends Model
{
    protected $table = 'care_histories';

    protected $fillable = [
        'student_id',
        'content',
        'status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    /**
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class,'student_id','id');
    }
}