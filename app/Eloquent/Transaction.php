<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Transaction
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $student_profile_id
 * @property string $code
 * @property bool $is_debt
 * @property int $amount
 * @property string $note
 * @property int $approval_status
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property int $deleted_time
 * @property int $deleted_by
 */
class Transaction extends Model
{
    use SoftDeletedTime;

    protected $table = 'transactions';

    protected $fillable = [
        'student_profile_id',
        'code',
        'is_debt',
        'amount',
        'note',
        'approval_status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_time',
        'deleted_by',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id', 'student_profile_id');
    }
}
