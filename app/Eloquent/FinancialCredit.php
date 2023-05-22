<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Financial Credit
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $student_profile_id
 * @property int $transaction_id
 * @property int $amount
 * @property int $purpose
 * @property int $no
 * @property string $note
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property int $deleted_time
 * @property int $deleted_by
 */
class FinancialCredit extends Model
{
    use SoftDeletedTime;

    protected $table = 'financial_credits';

    protected $fillable = [
        'student_profile_id',
        'transaction_id',
        'amount',
        'purpose',
        'no',
        'note',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_time',
        'deleted_by',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
    
    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id', 'student_profile_id');
    }

}
