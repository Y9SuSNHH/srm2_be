<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class StudentReceivable
 * @package App\Models
 *
 * @property $id
 * @property $student_profile_id
 * @property $receivable
 * @property $purpose
 * @property $learning_wave_number
 * @property $note
 * @property $created_by
 * @property $updated_by
 */

class StudentReceivable extends Model
{
    protected $table = 'student_receivables';

    protected $fillable = [
        'id',
        'student_profile_id',
        'receivable',
        'purpose',
        'learning_wave_number',
        'note',
        'created_by',
        'updated_by',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id','student_profile_id');
    }

    public function getProfile(): HasOneThrough
    {
        return $this->hasOneThrough(Profile::class, StudentProfile::class, 'id', 'id', 'student_profile_id', 'profile_id');
    }

    public function studentReceivablesPrev(): HasMany
    {
        return $this->hasMany(StudentReceivable::class, 'student_profile_id', 'student_profile_id');
    }
    /**
     * @return HasOne
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class, 'id', 'student_profile_id');
    }
}
