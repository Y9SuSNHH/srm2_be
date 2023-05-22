<?php

namespace App\Http\Domain\Workflow\Model\Workflow;

use App\Helpers\Json;
use App\Helpers\Traits\CamelArrayAble;
use App\Http\Domain\Workflow\Model\WorkflowStructure\WorkflowStructure;
use App\Http\Enum\ApprovalStatus;
use App\Http\Enum\WorkStatus;

/**
 * Class Workflow
 * @package App\Http\Domain\Workflow\Model\Workflow
 *
 * @property int $id
 * @property int $approval_status
 * @property bool $is_close
 * @property string $title
 * @property string $description
 * @property int $approval_step
 * @property WorkflowStructure $workflow_structure
 * @property array $workflow_values
 * @property int $approval_id
 */
class Workflow extends Json
{
    use CamelArrayAble;

    public $id;
    public $approval_status;
    public $approval_status_name;
    public $is_close;
    public $title;
    public $description;
    public $approval_step;
    public $workflow_structure;
    public $workflow_values;
    public $approval_id;
    public $workflow_approvals;
    public $work_status;
    public $work_status_name;
    public $preview_data;

    public function __construct($argument = null)
    {
        /** @var \App\Eloquent\Workflow $argument */
        $work_status = optional($argument->backlog)->work_status;
        parent::__construct(array_merge($argument->toArray(), [
            'workflow_structure' => new WorkflowStructure($argument->workflowStructure),
            'is_close' => (bool)$argument->is_close,
            'approval_status_name' => ApprovalStatus::fromOptional($argument->approval_status)->getLang(),
            'work_status' => $work_status,
            'work_status_name' => WorkStatus::fromOptional($work_status)->getLang(),
            'preview_data' => $argument->student ? new Student($argument->student) : null,
        ]));
    }
}
