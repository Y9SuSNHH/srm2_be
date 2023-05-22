<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class RequestStructure
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property string $name
 * @property string $description
 * @property int $apply_div
 * @property boolean $is_custom_form
 * @property int $apply_table_div
 * @property int $status_complete
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 */
class RequestStructure extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'request_structures';

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'apply_div',
        'is_custom_form',
        'apply_table_div',
        'status_complete',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'is_custom_form' => 'boolean',
    ];
}
