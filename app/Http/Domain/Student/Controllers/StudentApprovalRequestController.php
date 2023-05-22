<?php

namespace App\Http\Domain\Student\Controllers;

use App\Http\Domain\Student\Requests\Student\ApproveStudentUpdateRequest;
use App\Http\Domain\Student\Services\StudentService;
use Laravel\Lumen\Routing\Controller;

class StudentApprovalRequestController
{
    /**
     * @param int $student_id
     * @param ApproveStudentUpdateRequest $request
     * @param StudentService $service
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function workflowStore(int $student_id, ApproveStudentUpdateRequest $request, StudentService $service)
    {
        $request->throwJsonIfFailed();
        $workflow = $service->createStudentApprovalRequest($student_id, $request);

        if (!$workflow) {
            return json_response(false, [], '');
        }

        return json_response(true, $workflow);
    }
}
