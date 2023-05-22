<?php

namespace App\Http\Domain\Workflow\Controllers;

use App\Http\Domain\Common\Requests\BaseSearchRequest;
use App\Http\Domain\Workflow\Repositories\Workflow\WorkflowRepositoryInterface;
use App\Http\Domain\Workflow\Requests\WorkflowBulkUpdateRequest;
use App\Http\Domain\Workflow\Services\WorkflowService;

class WorkflowController
{
    /**
     * @param WorkflowBulkUpdateRequest $request
     * @param WorkflowService $service
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function bulkUpdate(WorkflowBulkUpdateRequest $request, WorkflowService $service): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response((bool)$service->bulkApprove($request->validated()));
    }

    /**
     * @param BaseSearchRequest $request
     * @param WorkflowRepositoryInterface $workflow_repository
     * @return \Illuminate\Http\JsonResponse
     */
    public function approved(BaseSearchRequest $request, WorkflowRepositoryInterface $workflow_repository): \Illuminate\Http\JsonResponse
    {
        return json_response(true, $workflow_repository->getAccept($request));
    }
}
