<?php

namespace App\Http\Domain\Workflow\Services;

use App\Eloquent\Workflow as EloquentWorkflow;
use App\Http\Domain\Common\Model\Backlog\Backlog as ModelBacklog;
use App\Http\Domain\Common\Model\Backlog\Reference;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use App\Http\Domain\Common\Services\BacklogService;
use App\Http\Domain\Workflow\Form\WorkflowFormInterface;
use App\Http\Domain\Workflow\Model\Workflow\Workflow as ModelWorkflow;
use App\Http\Domain\Workflow\Model\WorkflowStructure\WorkflowStructure as ModelWorkflowStructure;
use App\Http\Domain\Workflow\Repositories\WorkflowApproval\WorkflowApprovalRepository;
use App\Http\Domain\Workflow\Repositories\WorkflowApproval\WorkflowApprovalRepositoryInterface;
use App\Http\Domain\Workflow\Repositories\Workflow\WorkflowRepository;
use App\Http\Domain\Workflow\Repositories\Workflow\WorkflowRepositoryInterface;
use App\Http\Domain\Workflow\Repositories\WorkflowStructure\WorkflowStructureRepository;
use App\Http\Domain\Workflow\Repositories\WorkflowStructure\WorkflowStructureRepositoryInterface;
use App\Http\Enum\ApprovalStatus;
use App\Http\Enum\ReferenceType;
use App\Http\Enum\WorkDiv;
use App\Http\Enum\WorkStatus;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowService
{
    /** @var WorkflowRepository */
    private $workflow_repository;
    /** @var WorkflowStructureRepository */
    private $workflow_structure_repository;
    /** @var WorkflowApprovalRepository */
    private $workflow_approval_repository;

    public function __construct(
        WorkflowRepositoryInterface $workflow_repository,
        WorkflowApprovalRepositoryInterface $workflow_approval_repository,
        WorkflowStructureRepositoryInterface $workflow_structure_repository)
    {
        $this->workflow_repository = $workflow_repository;
        $this->workflow_structure_repository = $workflow_structure_repository;
        $this->workflow_approval_repository = $workflow_approval_repository;
    }

    /**
     * @param int $apply_div
     * @param string|null $alias
     * @return array
     */
    public function getStructures(int $apply_div, string $alias = null): array
    {
        if (!empty($alias)) {
            $workflow_structure = $this->workflow_structure_repository->getByAlias($alias);
            return [$workflow_structure];
        }

        return $this->workflow_structure_repository->getByApplyDiv($apply_div);
    }

    /**
     * @param ModelWorkflowStructure $workflow_structure
     * @param WorkflowFormInterface $workflow_form
     * @return EloquentWorkflow|null
     */
    public function createWorkflow(ModelWorkflowStructure $workflow_structure, WorkflowFormInterface $workflow_form): ?EloquentWorkflow
    {
        $workflow = null;

        try {
            DB::transaction(function () use ($workflow_structure, $workflow_form, &$workflow) {
                /** @var EloquentWorkflow $workflow */
                $workflow = $this->workflow_repository->create($workflow_structure, $workflow_form);
            });
        } catch (\Exception $exception) {
            Log::debug('Fail to create workflow and relation', compact('workflow_structure', 'workflow_form'));
            Log::error($exception->getMessage());
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $exception->getMessage()]));
        }

        return $workflow;
    }

    /**
     * @param array $input
     * @return bool
     */
    public function bulkApprove(array $input): bool
    {
        $result = false;

        try {
            DB::transaction(function () use ($input, &$result) {
                $workflow_accepts = array_filter($input['workflow'] ?? [], function ($item) {
                    return (int)$item['approval_status'] === ApprovalStatus::ACCEPT;
                });

                if (!empty($workflow_accepts)) {
                    $this->bulkAccept(array_column($workflow_accepts, 'approval_id'), array_column($workflow_accepts, 'id'));
                }

                $workflow_rejects = array_filter($input['workflow'] ?? [], function ($item) {
                    return (int)$item['approval_status'] === ApprovalStatus::REJECT;
                });

                if ($workflow_rejects) {
                    $this->bulkReject(array_column($workflow_rejects, 'approval_id'), array_column($workflow_rejects, 'id'));
                }

                $result = true;
            });
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage(), $exception->getTrace());
            throw_json_response($exception->getMessage());
        }

        return $result;
    }

    /**
     * @param int $id
     * @return ModelWorkflow|null
     */
    public function find(int $id): ?ModelWorkflow
    {
        return $this->workflow_repository->findOrFail($id);
    }

    public function closeWorkflow(int $workflow_id, int $user_id)
    {
        return $this->workflow_repository->update($workflow_id, [
            'updated_by' => $user_id,
            'approval_status' => ApprovalStatus::DONE,
            'is_close' => TRUE,
        ]);
    }

    /**
     * @param BaseSearchRequest $request
     * @return \App\Helpers\LengthAwarePaginator
     */
//    public function getApprovedList(BaseSearchRequest $request): \App\Helpers\LengthAwarePaginator
//    {
//        $paginator = $this->workflow_repository->getAccept($request);
//        $paginator->getCollection()->transform(function ($workflow) {
//            /** @var EloquentWorkflow $workflow */
//            $workflow_model = new ModelWorkflow($workflow);
//            $form = null;
//            $model = $workflow->workflowStructure->model;
//
//            if (\App\Http\Domain\Student\Form\StudentForm::class === $model) {
//                /** @var \App\Http\Domain\Student\Form\StudentForm $form */
//                $form = app($model);
//                $form->setValue($workflow->workflowValues->toArray());
//            }
//
//            return [
//                'workflow' => $workflow_model->toArray(),
//                'form' => optional($form)->getValueCamels(),
//            ];
//        });
//
//        return $paginator;
//    }

    /**
     * @param array $approval_ids
     * @param array $workflow_ids
     * @throws \ReflectionException
     */
    private function bulkReject(array $approval_ids, array $workflow_ids)
    {
        if ($this->workflow_approval_repository->rejectFinal($approval_ids)) {
            $this->workflow_repository->updateProcess($workflow_ids, ApprovalStatus::REJECT, auth()->getId(), true);
        }
    }

    /**
     * @param array $approval_ids
     * @param array $workflow_ids
     * @throws \ReflectionException
     */
    private function bulkAccept(array $approval_ids, array $workflow_ids)
    {
        if ($this->workflow_approval_repository->approvalFinal($approval_ids)) {
            $this->workflow_repository->updateProcess($workflow_ids, ApprovalStatus::IN_PROCESS, auth()->getId());
            $workflows = $this->workflow_repository->getAllApproved($workflow_ids);
            $backlog_attributes = $workflows->map(function ($workflow) {
                /** @var EloquentWorkflow $workflow */
                return[
                    'user_id' => auth()->getId(),
                    'school_id' => school()->getId(),
                    'work_div' => WorkDiv::APPROVAL_EDIT_STUDENT,
                    'work_status' => WorkStatus::WAIT,
                    'work_payload' => $workflow->toJson(),
                    'reference' => new Reference([['type' => ReferenceType::WORKFLOW, 'id' => $workflow->id]], $workflow->title)
                ];
            })->toArray();

            /** @var BacklogService $backlog_service */
            $backlog_service = app()->service(BacklogService::class);
            /** @var ModelBacklog[]|\Illuminate\Support\Collection $backlogs */
            $backlogs = $backlog_service->push($backlog_attributes);
            $attributes = $backlogs->transform(function ($backlog) {
                /** @var ModelBacklog $backlog */
                return array_fill_keys($backlog->reference->idList(), $backlog->id);
            })->toArray();
            $attributes = array_replace(...$attributes);
            $this->workflow_repository->backlogUpdate($attributes);
        }
    }
}
