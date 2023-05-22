<?php

namespace App\Http\Domain\Workflow\Repositories\WorkflowApproval;

use App\Eloquent\Workflow as EloquentWorkflow;
use App\Eloquent\WorkflowApproval as EloquentWorkflowApproval;
use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\Interfaces\ThrowIfNotAbleInterface;
use App\Helpers\LengthAwarePaginator;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Workflow\Model\Workflow\Workflow;
use App\Http\Enum\ApprovalStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowApprovalRepository implements WorkflowApprovalRepositoryInterface, ThrowIfNotAbleInterface
{
    use ThrowIfNotAble;

    /** @var EloquentWorkflowApproval */
    private $eloquent_model;

    public function __construct()
    {
        $this->eloquent_model = EloquentWorkflowApproval::getModel();
    }

    /**
     * only need 1 user to approve
     * other users are ignored
     * workflow_approval_steps.is_fully is false
     *
     * @param array $workflow_approval_ids
     * @return bool
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function approvalFinal(array $workflow_approval_ids): bool
    {
        return $this->updateAble(EloquentWorkflowApproval::class, function () use ($workflow_approval_ids) {
            $result = $this->eloquent_model->newQuery()
                ->whereIn('id', $workflow_approval_ids)
                ->update([
                    'approval_status' => ApprovalStatus::ACCEPT,
                    'is_final' => true,
                    'approval_at' => Carbon::now()
                ]);

            if (!$result) {
                Log::debug('Fail to update workflow_approvals', ['workflow_approval_ids' => $workflow_approval_ids, 'approval_status' => ApprovalStatus::ACCEPT, 'is_final' => true]);
                throw new \Exception('Fail to update workflow_approvals');
            }

            $result = $this->closeRemainingSteps();

            return (bool)$result;
        });

    }

    /**
     * @param array $workflow_approval_ids
     * @return bool
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function rejectFinal(array $workflow_approval_ids): bool
    {
        return $this->updateAble(EloquentWorkflowApproval::class, function () use ($workflow_approval_ids) {
            $result = $this->eloquent_model->newQuery()
                ->whereIn('id', $workflow_approval_ids)
                ->update(['approval_status' => ApprovalStatus::REJECT, 'is_final' => true, 'approval_at' => Carbon::now()]);

            if (!$result) {
                Log::debug('Fail to reject workflow_approvals', ['workflow_approval_ids' => $workflow_approval_ids, 'approval_status' => ApprovalStatus::REJECT, 'is_final' => true]);
                throw new \Exception('Fail to reject workflow_approvals');
            }

            $result = $this->closeRemainingSteps();

            return (bool)$result;
        });

    }
    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllPending(): \Illuminate\Database\Eloquent\Collection|array
    {
        $workflows = $this->pendingQuery()->get();
        $workflows->transform(function ($workflow) {
            return new Workflow($workflow);
        });

        return $workflows;
    }

    public function fetchPending(PaginateSearchRequest $request): LengthAwarePaginator
    {
        $query = $this->pendingQuery();
        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        $paginate->getCollection()->transform(function ($workflow) {
            return new Workflow($workflow);
        });

        return $paginate;
    }

    /**
     * @param bool $full_approval
     * @return Builder
     */
    private function pendingQuery($full_approval = false): Builder
    {
        return EloquentWorkflow::query()
            ->with([
                'workflowStructure:id,apply_div,is_custom_form,model',
                'workflowValues:workflow_id,target,value',
                'student' => function($query) {
                    /** @var Builder $query */
                    $query->with(['studentProfile:id,documents', 'classrooms:id,code'])->select(['id', 'student_status', 'student_code', 'student_profile_id']);
                },
            ])
            ->join('workflow_approvals', 'workflow_approvals.workflow_id', '=', 'workflows.id')
            ->join('workflow_approval_steps', 'workflow_approvals.workflow_approval_step_id', '=', 'workflow_approval_steps.id')
            ->where('workflow_approvals.approval_status', ApprovalStatus::PENDING)
            ->where('workflow_approvals.user_id', auth()->getId())
            ->whereRaw('"workflows"."is_close"::bool=false')
            ->whereRaw('"workflow_approval_steps"."is_fully"::bool='. ($full_approval ? 'true' : 'false'))
            ->whereExists(function ($query) {
                /** @var Builder $query */
                $query->selectRaw('1')->fromSub(function ($query) {
                    /** @var Builder $query */
                    $query->select(['wa.workflow_id as wid', DB::raw('min(was.approval_step)  as "step"')])
                        ->from('workflow_approvals as wa')
                        ->join('workflow_approval_steps as was', 'workflow_approval_step_id', '=', 'was.id')
                        ->where('user_id', auth()->getId())
                        ->where('approval_status', ApprovalStatus::PENDING)
                        ->groupBy('wa.workflow_id');
                }, 'table_tmp')
                    ->whereRaw('table_tmp.wid=workflow_approvals.workflow_id')
                    ->whereRaw('workflow_approval_steps.approval_step = table_tmp.step');
            })
            ->orderBy('workflows.created_at')
            ->groupBy('workflows.id', 'workflow_approvals.id', 'workflow_approval_steps.approval_step', 'workflow_approvals.id')
            ->select([
                'workflows.id',
                'workflows.workflow_structure_id',
                'workflows.reference_id',
                'workflows.approval_status',
                'workflows.is_close',
                'workflows.title',
                'workflows.description',
                'workflow_approval_steps.approval_step',
                'workflow_approvals.id as approval_id',
            ]);
    }

    /**
     * @return int
     */
    private function closeRemainingSteps(): int
    {
        return $this->eloquent_model->newQuery()
            ->where('approval_status', ApprovalStatus::PENDING)
            ->whereExists(function ($query) {
                /** @var Builder $query */
                $query->selectRaw(1)->from('workflow_approvals as wa')
                    ->whereRaw('wa.workflow_id = workflow_approvals.workflow_id')
                    ->whereRaw('wa.workflow_approval_step_id = workflow_approvals.workflow_approval_step_id')
                    ->whereRaw('wa.is_final::bool = true');
            })->update(['approval_status' => ApprovalStatus::CLOSE]);
    }
}
