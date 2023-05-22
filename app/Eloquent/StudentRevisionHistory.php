<?php

namespace App\Eloquent;

/**
 * Class StudentRevisionHistory
 * @package App\Eloquent
 *
 * @property $id
 * @property $student_id
 * @property $type
 * @property $value
 * @property \Carbon\Carbon $began_date
 * @property \Carbon\Carbon $began_at
 * @property \Carbon\Carbon $ended_at
 * @property $reference_type
 * @property $reference_id
 */
class StudentRevisionHistory extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'value',
        'began_date',
        'began_at',
        'ended_at',
        'reference_type',
        'reference_id',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['began_date', 'began_at', 'ended_at'];
    
}