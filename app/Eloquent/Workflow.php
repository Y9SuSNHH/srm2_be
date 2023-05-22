<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class Workflow
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property int $workflow_structure_id
 * @property int $approval_status
 * @property int $is_close
 * @property string $title
 * @property string $description
 * @property int $backlog_id
 * @property int $reference_id
 * @property $created_at
 * @property int $created_by
 * @property $updated_at
 * @property int $updated_by
 *
 * @property WorkflowStructure $workflowStructure
 * @property WorkflowApproval[]|\Illuminate\Database\Eloquent\Collection $workflowApprovals
 * @property WorkflowValue[]|\Illuminate\Database\Eloquent\Collection $workflowValues
 * @property Backlog $backlog
 * @property Student|null $student
 */
class Workflow extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'workflows';

    protected $fillable = [
        'created_by',
        'updated_by',
        'school_id',
        'workflow_structure_id',
        'approval_status',
        'is_close',
        'title',
        'description',
        'backlog_id',
        'reference_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStructure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkflowStructure::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowApprovals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkflowApproval::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowValues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkflowValue::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function backlog(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Backlog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class, 'reference_id');
    }
}
