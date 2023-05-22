<?php

namespace App\Http\Domain\Workflow\Repositories\Workflow;

use App\Eloquent\Workflow as EloquentWorkflow;
use App\Eloquent\WorkflowApproval as EloquentWorkflowApproval;
use App\Eloquent\WorkflowFormValue as EloquentWorkflowFormValue;
use App\Eloquent\WorkflowStructure;
use App\Eloquent\WorkflowValue as EloquentWorkflowValue;
use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\Interfaces\ThrowIfNotAbleInterface;
use App\Helpers\LengthAwarePaginator;
use App\Http\Domain\Workflow\Form\WorkflowFormInterface;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Workflow\Model\Workflow\Workflow as ModelWorkflow;
use App\Http\Domain\Workflow\Model\WorkflowStructure\WorkflowStructure as ModelWorkflowStructure;
use App\Http\Enum\ApprovalStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowRepository implements WorkflowRepositoryInterface, ThrowIfNotAbleInterface
{
    use ThrowIfNotAble;

    /**
     * @param ModelWorkflowStructure $workflow_structure
     * @param WorkflowFormInterface $workflow_form
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function create(ModelWorkflowStructure $workflow_structure, WorkflowFormInterface $workflow_form): mixed
    {
        /** @var \App\Http\Domain\Student\Form\StudentForm $workflow_form */
        return $this->createAble([
                EloquentWorkflow::class,
                EloquentWorkflowValue::class,
                EloquentWorkflowFormValue::class,
                EloquentWorkflowApproval::class,
            ], function () use ($workflow_form, $workflow_structure) {
            /** @var EloquentWorkflow $workflow */
            $workflow = EloquentWorkflow::getModel()->create($workflow_form->workflowAttributes());

            if (!$workflow) {
                Log::debug('Fail to create workflow', compact('workflow_structure_id', 'title', 'values'));
                throw new \Exception('Fail to create workflow');
            }

            log_debug('success to create workflow', ['workflow' => $workflow]);

            if (!$workflow_structure->is_custom_form) {
                $attributes = array_map(function ($value) use ($workflow_structure, $workflow) {
                    return [
                        'workflow_id' => $workflow->id,
                        'workflow_structure_id' => $workflow_structure->id,
                        ...$value
                    ];
                }, $workflow_form->workflowValueAttributes());

                if (!EloquentWorkflowValue::query()->insert($attributes)) {
                    Log::debug('fail to insert workflow_values');
                    throw new \Exception('Create workflow fail');
                }

                log_debug('success to insert workflow_values', ['workflow_values' => $attributes]);
            } else {
                $attributes = array_map(function ($value) use ($workflow) {
                    return [
                        'workflow_id' => $workflow->id,
                        ...$value
                    ];

                }, $workflow_form->workflowValueAttributes());

                if (EloquentWorkflowFormValue::query()->insert($attributes)) {
                    Log::debug('fail to insert workflow_form_values');
                    throw new \Exception('Create workflow fail');
                }

                log_debug('success to insert workflow_form_values', ['workflow_form_values' => $attributes]);
            }

            $attributes = array_map(function ($approval) use ($workflow) {
                return [
                    'workflow_id' => $workflow->id,
                    'approval_status' => ApprovalStatus::PENDING,
                    ...$approval
                ];

            }, $workflow_structure->approval_steps);

            EloquentWorkflowApproval::query()->insert($attributes);
            log_debug('success to insert workflow_approvals', ['workflow_approvals' => $attributes]);

            $workflow->load(['workflowValues', 'workflowApprovals']);
            return $workflow;
        });
    }

    /**
     * @param array $ids
     * @param int $approval_status
     * @param int $updated_by
     * @param bool $is_close
     * @return mixed
     * @throws \ReflectionException
     */
    public function updateProcess(array $ids, int $approval_status, int $updated_by, bool $is_close = false): mixed
    {
        return $this->updateAble(EloquentWorkflow::class, function () use ($ids, $approval_status, $is_close, $updated_by) {
            return EloquentWorkflow::query()->whereIn('id', $ids)->update([
                'approval_status' => $approval_status,
                'updated_by' => $updated_by,
                'is_close' => $is_close,
            ]);
        });
    }

    /**
     * @param array $attributes
     * @return bool
     */
    public function backlogUpdate(array $attributes): bool
    {
        return DB::statement('update workflows  set backlog_id = u.backlog from (values '.
            implode(', ', array_map(function ($val, $key) {
                return "($key, $val)";
            }, $attributes, array_keys($attributes))).
            ') as u(id, backlog) where workflows.id in ('.
            implode(', ', array_keys($attributes)).
            ') and workflows.id = u.id');
    }

    public function update(int $id, array $attributes)
    {
        return $this->updateAble(EloquentWorkflow::class, function () use ($id, $attributes) {
            return EloquentWorkflow::query()->where('id', $id)->update($attributes);
        });
    }

    /**
     * @param array|null $ids
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllApproved(array $ids = null): \Illuminate\Database\Eloquent\Collection|array
    {
        $query = EloquentWorkflow::query()
            ->where('approval_status', ApprovalStatus::IN_PROCESS)
            ->whereRaw('is_close::bool=false')
            ->whereNotExists(function ($query) {
                /** @var Builder $query */
                $query->selectRaw(1)->from('workflow_approvals')
                    ->where('approval_status', ApprovalStatus::PENDING)
                    ->whereRaw('workflows.id=workflow_approvals.workflow_id');
            });

        if ($ids) {
            $query->whereIn('id', $ids);
        }

        return $query->get();
    }

    /**
     * @param int $id
     * @return ModelWorkflow|null
     */
    public function findOrFail(int $id)
    {
        /** @var EloquentWorkflow $workflow */
        $workflow = EloquentWorkflow::query()->with(['workflowStructure:id,is_custom_form,model'])->findOrFail($id);
        $is_custom_form = $workflow->workflowStructure->is_custom_form;

        if (!$is_custom_form) {
            $workflow->load(['workflowValues' => function($query) use ($workflow) {
                /** @var Builder $query */
                $query->where('workflow_structure_id', $workflow->workflowStructure->id);
            }]);
        }

        return new ModelWorkflow($workflow);
    }

    /**
     * @param PaginateSearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAccept(PaginateSearchRequest $request): LengthAwarePaginator
    {
        $query = EloquentWorkflow::query()
            ->with([
                'workflowStructure:id,apply_div,is_custom_form,model',
                'workflowValues:workflow_id,target,value',
                'workflowApprovals' => function($query) {
                    /** @var Builder $query */
                    $query->with('staff:user_id,fullname')
                        ->where('workflow_approvals.approval_status', ApprovalStatus::ACCEPT)
                        ->select(['workflow_id', 'comment', 'approval_at', 'user_id']);
                },
                'backlog:id,work_status',
            ])
            ->whereHas('workflowApprovals', function ($query) {
                /** @var Builder $query */
                $query->where('workflow_approvals.approval_status', ApprovalStatus::ACCEPT);
            })
            ->orderByDesc('updated_at');

        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        $paginate->getCollection()->transform(function ($workflow) {
            return new ModelWorkflow($workflow);
        });

        return $paginate;
    }
}
