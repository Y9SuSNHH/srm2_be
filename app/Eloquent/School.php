<?php

namespace App\Eloquent;

/**
 * Class School
 * @package App\Eloquent
 *
 * @property int $id
 * @property string $school_code
 * @property string $school_name
 * @property int $school_status
 * @property string $service_name
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 */
class School extends Model
{
    protected $table = 'schools';

    protected $fillable = [
        'school_code',
        'school_name',
        'school_status',
        'service_name',
        'created_by',
        'updated_by',
    ];
}
