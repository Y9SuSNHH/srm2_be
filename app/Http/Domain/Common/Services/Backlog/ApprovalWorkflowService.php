<?php

namespace App\Http\Domain\Common\Services\Backlog;

use App\Helpers\BacklogHandled;
use App\Http\Domain\Common\Model\Backlog\Backlog;
use App\Http\Domain\Common\Services\BacklogService;
use App\Http\Domain\Student\Services\StudentService;
use App\Http\Domain\Workflow\Services\WorkflowService;
use App\Http\Enum\StudentRevisionHistoryType;
use App\Http\Enum\WorkStatus;
use Illuminate\Support\Facades\Log;

class ApprovalWorkflowService extends BacklogHandled
{
    /**
     * @param int $backlog_id
     * @param BacklogService $backlog_service
     * @throws \Throwable
     */
    public function handle(int $backlog_id, BacklogService $backlog_service)
    {
        try {
            app()->configAuth();
            auth()->fakeDefaultsGuard('cmd');
            /** @var Backlog|null $backlog */
            $backlog = $backlog_service->find($backlog_id);

            if ($backlog && WorkStatus::WAIT === $backlog->work_status) {

                /** @var WorkflowService $workflow_service */
                $workflow_service = app()->service(WorkflowService::class);
                // get workflow
                $workflow = $workflow_service->find($backlog->work_payload['id']);

                /** @var \App\Http\Domain\Student\Form\StudentForm $student_form */
                $student_form = app($workflow->workflow_structure->model, [
                    'workflow_id' => $workflow->id,
                    'workflow_structure_id' => $workflow->workflow_structure->id,
                    'title' => $workflow->title,
                    'description' => $workflow->description,
                ]);
                $student_form->workflowValues($workflow->workflow_values);
                /** @var StudentService $student_service */
                $student_service = app()->service(StudentService::class);
                // get student information
                $student = $student_service->getById($student_form->getStudentId());
                $student_service->update($student_form->getStudentId(), $student_form->getApprovalStudentAttribute($backlog->user_id));

                /** @var \App\Http\Domain\Common\Services\StudentHistoryService $student_history_service */
                $student_history_service = app()->service(\App\Http\Domain\Common\Services\StudentHistoryService::class);

                if ($student_form->getClassroomId()) {
                    $student_history_service->saveStudentClassroomLog(
                        $student_form->getStudentId(),
                        $student_form->getClassroomId(),
                        $backlog->getCreatedAt(),
                        null,
                        $backlog->user_id
                    );
                }

                if ($student_form->getStudentStatus()) {
                    $student_history_service->saveStudentRevisionHistories(
                        StudentRevisionHistoryType::STUDENT_STATUS,
                        $student_form->getStudentId(),
                        $student_form->getStudentStatus(),
                        $backlog->getCreatedAt(),
                        null,
                        $backlog->user_id
                    );
                }

                $documents = json_decode($student->documents->toJson(), true);
                $student_service->updateStudentProfile($student_form->getStudentId(), $student_form->getApprovalStudentProfiles($backlog->user_id, $documents));
                $workflow_service->closeWorkflow($workflow->id, $backlog->user_id);
                $backlog_service->setComplete($backlog_id);
            }
        } catch (\Exception $e) {
            $backlog_service->setFail($backlog_id, $e->getMessage());
            Log::debug($e->getMessage(), $e->getTrace());
        }
    }
}
