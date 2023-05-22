<?php

namespace App\Http\Domain\Workflow\Repositories\WorkflowApproval;

use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\LengthAwarePaginator;

interface WorkflowApprovalRepositoryInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getAllPending(): \Illuminate\Database\Eloquent\Collection|array;

    /**
     * @param PaginateSearchRequest $request
     * @return LengthAwarePaginator
     */
    public function fetchPending(PaginateSearchRequest $request): LengthAwarePaginator;

    /**
     * @param array $workflow_approval_ids
     * @return mixed
     */
    public function approvalFinal(array $workflow_approval_ids): mixed;

}
