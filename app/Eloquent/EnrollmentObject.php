<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class EnrollmentObject
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property string $code
 * @property string $classification
 * @property string $name
 * @property string $shortcode
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 */
class EnrollmentObject extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'enrollment_objects';

    protected $fillable = [
        'school_id',
        'code',
        'classification',
        'name',
        'shortcode',
        'created_by',
        'updated_by',
    ];
}
