<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class Request
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property int $request_structure_id
 * @property int $approval_status
 * @property boolean $is_close
 * @property string $title
 * @property string $description
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 *
 */
class Request extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'requests';

    protected $fillable = [
        'school_id',
        'request_structure_id',
        'approval_status',
        'is_close',
        'title',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_close' => 'boolean',
    ];
}
