<?php

namespace App\Http\Domain\Workflow\Controllers;

use App\Http\Domain\Common\Requests\BaseSearchRequest;
use App\Http\Domain\Workflow\Repositories\WorkflowApproval\WorkflowApprovalRepositoryInterface;

/**
 * Class WorkflowRequestController
 * @package App\Http\Domain\Workflow\Controllers
 */
class WorkflowRequestController
{
    /**
     * @param WorkflowApprovalRepositoryInterface $repository
     * @param BaseSearchRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(WorkflowApprovalRepositoryInterface $repository, BaseSearchRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();

        return json_response(true, $repository->getAllPending());
    }

    public function show()
    {

    }

    public function approval()
    {

    }
}