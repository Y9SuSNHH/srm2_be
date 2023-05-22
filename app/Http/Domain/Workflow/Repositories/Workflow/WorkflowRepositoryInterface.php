<?php

namespace App\Http\Domain\Workflow\Repositories\Workflow;

use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\LengthAwarePaginator;
use App\Http\Domain\Workflow\Form\WorkflowFormInterface;
use App\Http\Domain\Workflow\Model\WorkflowStructure\WorkflowStructure as ModelWorkflowStructure;

interface WorkflowRepositoryInterface
{
    /**
     * @param ModelWorkflowStructure $workflow_structure
     * @param WorkflowFormInterface $workflow_form
     * @return mixed
     */
    public function create(ModelWorkflowStructure $workflow_structure, WorkflowFormInterface $workflow_form): mixed;

    /**
     * @param array $ids
     * @param int $approval_status
     * @param int $updated_by
     * @return mixed
     */
    public function updateProcess(array $ids, int $approval_status, int $updated_by): mixed;

    /**
     * @param array $attributes
     * @return bool
     */
    public function backlogUpdate(array $attributes): bool;

    /**
     * @param array|null $ids
     * @return mixed
     */
    public function getAllApproved(array $ids = null): mixed;

    /**
     * @param PaginateSearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAccept(PaginateSearchRequest $request): LengthAwarePaginator;
}
