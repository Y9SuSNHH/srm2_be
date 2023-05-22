<?php

namespace App\Http\Domain\Student\Form;

use App\Http\Domain\Workflow\Form\WorkflowFormInterface;

class ChangeClassroomForm implements WorkflowFormInterface
{
    public function workflowAttributes(): array
    {
        return [];
    }

    public function workflowApprovalAttributes(): array
    {
        return [];
    }

    public function workflowValueAttributes(): array
    {
        return [];
    }

    public function workflowValues(array $values = null): mixed
    {
        // TODO: Implement workflowValues() method.
    }
}