<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class RequestApprovalStep
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $request_structure_id
 * @property int $approval_step
 * @property int $approval_group_user_id
 * @property boolean $is_fully
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 */
class RequestApprovalStep extends Model
{
    use SoftDeletedTime;

    protected $table = 'request_approval_steps';

    protected $fillable = [
        'request_structure_id',
        'approval_step',
        'approval_group_user_id',
        'is_fully',
        'created_by',
        'updated_by',
    ];
}