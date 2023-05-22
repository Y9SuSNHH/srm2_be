<?php

namespace App\Eloquent;

/**
 * Class WorkflowApproval
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $workflow_id
 * @property int $workflow_approval_step_id
 * @property int $user_id
 * @property int $approval_status
 * @property int $is_final
 * @property int $comment
 * @property \Carbon\Carbon $approval_at
 *
 * @property Workflow $workflow
 * @property Staff|null $staff
 */
class WorkflowApproval extends Model
{
    protected $table = 'workflow_approvals';

    public $timestamps = false;

    protected $fillable = [
        'workflow_id',
        'workflow_approval_step_id',
        'user_id',
        'approval_status',
        'is_final',
        'comment',
        'approval_at',
    ];

    protected $dates = ['approval_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowApprovalStep(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkflowApprovalStep::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function staff(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Staff::class, 'user_id', 'user_id');
    }
}
