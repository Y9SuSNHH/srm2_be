<?php

namespace App\Http\Domain\Workflow\Model\WorkflowStructure;

use App\Helpers\Json;
use App\Eloquent\WorkflowStructure as EloquentWorkflowStructure;

/**
 * Class WorkflowStructure
 * @package App\Http\Domain\Workflow\Model\WorkflowStructure
 *
 * @property $id
 * @property $name;
 * @property $description;
 * @property $apply_div;
 * @property $is_custom_form;
 * @property $apply_table_div;
 * @property $model;
 * @property $alias;
 * @property array $approval_steps;
 */
class WorkflowStructure extends Json
{
    public $id;
    public $name;
    public $description;
    public $apply_div;
    public $is_custom_form;
    public $apply_table_div;
    public $model;
    public $alias;
    public $approval_steps;

    public function __construct(EloquentWorkflowStructure $workflow_structure = null)
    {
        $approval_steps = [];
        $step = 0;
        /** @var \App\Eloquent\WorkflowApprovalStep $workflow_approval_step */
        foreach ($workflow_structure->workflowApprovalSteps as $workflow_approval_step) {
            $users = $workflow_approval_step->approvalGroupUser->users;

            if ($step + 1 === $workflow_approval_step->approval_step && $users->isNotEmpty()) {
                $step = $workflow_approval_step->approval_step;
                $approval_steps = [...$approval_steps, ...array_map(function ($id) use ($workflow_approval_step) {
                    return ['workflow_approval_step_id' => $workflow_approval_step->id, 'user_id' => $id];
                }, $users->pluck('id')->toArray())];
            }
        }

        parent::__construct(array_merge($workflow_structure->toArray(), ['approval_steps' => $approval_steps]));
    }
}
