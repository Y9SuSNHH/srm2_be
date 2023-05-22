<?php

namespace App\Http\Domain\Workflow\Repositories\WorkflowStructure;

use App\Http\Domain\Workflow\Model\WorkflowStructure\WorkflowStructure as ModelWorkflowStructure;

interface WorkflowStructureRepositoryInterface
{
    /**
     * @param string $alias
     * @return ModelWorkflowStructure
     */
    public function getByAlias(string $alias): ModelWorkflowStructure;

    /**
     * @param int $apply_div
     * @return array
     */
    public function getByApplyDiv(int $apply_div): array;
}
