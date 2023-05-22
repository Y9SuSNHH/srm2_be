<?php

namespace App\Http\Domain\Workflow\Repositories\WorkflowStructure;

use App\Eloquent\WorkflowStructure as EloquentWorkflowStructure;
use App\Http\Domain\Workflow\Model\WorkflowStructure\WorkflowStructure as ModelWorkflowStructure;
use Illuminate\Database\Eloquent\Builder;

class WorkflowStructureRepository implements WorkflowStructureRepositoryInterface
{
    /**
     * @param string $alias
     * @return ModelWorkflowStructure
     */
    public function getByAlias(string $alias): ModelWorkflowStructure
    {
        /** @var EloquentWorkflowStructure $workflow_structure */
        $workflow_structure = EloquentWorkflowStructure::query()
            ->with(['workflowApprovalSteps' => function($query) {
                /** @var Builder $query */
                $query->with(['approvalGroupUser' => function($query) {
                    /** @var Builder $query */
                    $query->with('users:id')->select(['id', 'name']);
                }])->orderBy('approval_step');
            }])->where('alias', $alias)->firstOrFail();

        return new ModelWorkflowStructure($workflow_structure);
    }

    /**
     * @param int $apply_div
     * @return array
     */
    public function getByApplyDiv(int $apply_div): array
    {
        return EloquentWorkflowStructure::query()
            ->with(['workflowApprovalSteps' => function($query) {
                /** @var Builder $query */
                $query->with(['approvalGroupUser' => function($query) {
                    /** @var Builder $query */
                    $query->with('users:id')->select(['id', 'name']);
                }])->orderBy('approval_step');
            }])->where('apply_div', $apply_div)
            ->get()->transform(function ($workflow_structure) {
                return new ModelWorkflowStructure($workflow_structure);
            })->toArray();
    }
}
