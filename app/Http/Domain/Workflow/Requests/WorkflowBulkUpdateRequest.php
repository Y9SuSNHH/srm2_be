<?php

namespace App\Http\Domain\Workflow\Requests;

use App\Helpers\Request;
use App\Http\Enum\ApprovalStatus;
use Illuminate\Validation\Rule;

class WorkflowBulkUpdateRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'workflow' => [
                'required',
                'array'
            ],
            'workflow.*.id' => [
                'required',
                'integer'
            ],
            'workflow.*.step' => [
                'required',
                'integer'
            ],
            'workflow.*.approval_id' => [
                'required',
                'integer'
            ],
            'workflow.*.approval_status' => [
                'required',
                Rule::in(ApprovalStatus::toArray())
            ],
        ];
    }
}
