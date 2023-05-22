<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class WorkflowStructure
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property string $name
 * @property string $alias
 * @property string $description
 * @property int $apply_div
 * @property int $is_custom_form
 * @property string $model
 * @property $created_at
 * @property int $created_by
 * @property $updated_at
 * @property int $updated_by
 *
 * @property WorkflowApprovalStep[]|\Illuminate\Database\Eloquent\Collection $workflowApprovalSteps
 */
class WorkflowStructure extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'workflow_structures';

    protected $fillable = [
        'created_by',
        'updated_by',
        'school_id',
        'name',
        'alias',
        'description',
        'apply_div',
        'is_custom_form',
        'model',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowApprovalSteps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkflowApprovalStep::class);
    }
}
