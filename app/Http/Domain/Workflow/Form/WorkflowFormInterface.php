<?php

namespace App\Http\Domain\Workflow\Form;

interface WorkflowFormInterface
{
    public function workflowAttributes(): array;
    public function workflowApprovalAttributes(): array;
    public function workflowValueAttributes(): array;
    public function workflowValues(array $values = null): mixed;
}
