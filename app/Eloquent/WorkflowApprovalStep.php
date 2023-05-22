<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class WorkflowApprovalStep
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $workflow_structure_id
 * @property int $approval_step
 * @property int $approval_group_user_id
 * @property int $is_fully
 * @property $created_at
 * @property int $created_by
 * @property $updated_at
 * @property int $updated_by
 *
 * @property ApprovalGroupUser $approvalGroupUser
 */
class WorkflowApprovalStep extends Model
{
    use SoftDeletedTime;

    protected $table = 'workflow_approval_steps';

    protected $fillable = [
        'created_by',
        'updated_by',
        'workflow_structure_id',
        'approval_step',
        'approval_group_user_id',
        'is_fully',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvalGroupUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApprovalGroupUser::class);
    }
}
