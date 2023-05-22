<?php

namespace App\Http\Domain\Common\Services;

use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\JobBacklog;
use App\Helpers\LengthAwarePaginator;
use App\Http\Domain\Common\Model\Backlog\Backlog as ModelBacklog;
use App\Http\Domain\Common\Repositories\Backlog\BacklogRepository;
use App\Http\Domain\Common\Repositories\Backlog\BacklogRepositoryInterface;
use App\Http\Enum\WorkStatus;
use App\Jobs\Backlog;
use Illuminate\Support\Facades\Queue;

class BacklogService
{
    /** @var BacklogRepository */
    private $backlog_repository;

    /**
     * BacklogService constructor.
     * @param BacklogRepositoryInterface $repository
     */
    public function __construct(BacklogRepositoryInterface $repository)
    {
        $this->backlog_repository = $repository;
    }

    /**
     * @param array $attributes
     * @return ModelBacklog|null
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function add(array $attributes): ?ModelBacklog
    {
        /** @var ModelBacklog $backlog */
        $backlog = $this->backlog_repository->create($attributes);

        if ($backlog) {
            $job_backlog = new JobBacklog();
            $job_backlog->set($backlog->work_div, [$backlog->id]);
            $job_backlog->execute();

            return $backlog;
        }

        return null;
    }

    /**
     * @param array $attributes
     * @return mixed
     * @throws \ReflectionException
     */
    public function push(array $attributes): mixed
    {
        /** @var ModelBacklog[]|\Illuminate\Support\Collection $backlogs */
        $backlogs = $this->backlog_repository->push($attributes);

        if ($backlogs) {
            $argument = $backlogs->groupBy('work_div')->map(function ($collection) {
                return $collection->pluck('id')->toArray();
            })->toArray();

            $job_backlog = new JobBacklog();

            foreach ($argument as $work_div => $id_list) {
                $job_backlog->set($work_div, $id_list);
            }

            $job_backlog->execute();

            return $backlogs;
        }

        return null;
    }

    /**
     * @param int $id
     * @return ModelBacklog|null
     * @throws \ReflectionException
     */
    public function find(int $id): ?ModelBacklog
    {
        return $this->backlog_repository->getById($id);
    }

    /**
     * @param int $id
     * @param array $attribute
     * @return bool
     */
    public function update(int $id, array $attribute): bool
    {
        return $this->backlog_repository->update($id, $attribute);
    }

    /**
     * @param int $id
     * @param string|null $note
     * @return bool
     */
    public function setComplete(int $id, string $note = null): bool
    {
        return $this->backlog_repository->update($id, array_filter(['work_status' => WorkStatus::COMPLETE, 'note' => $note]));
    }

    /**
     * @param int $id
     * @param string|null $note
     * @return bool
     */
    public function setFail(int $id, string $note = null): bool
    {
        return $this->backlog_repository->update($id, array_filter(['work_status' => WorkStatus::FAIL, 'note' => $note]));
    }

    /**
     * @param PaginateSearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(PaginateSearchRequest $request): LengthAwarePaginator
    {
        return $this->backlog_repository->getAll($request);
    }

    /**
     * @param array $backlogs
     * @return bool
     */
    public function reExecute(array $backlogs): bool
    {
        $backlogs = array_map('intval', $backlogs);
        $result = $this->backlog_repository->getWait($backlogs);

        if ($result) {
            $job_backlog = new JobBacklog($result);
            return $job_backlog->execute();
        }

        return false;
    }
}
