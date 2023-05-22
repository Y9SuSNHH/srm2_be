<?php

namespace App\Http\Domain\Common\Repositories\Backlog;

use App\Eloquent\Backlog as EloquentBacklog;
use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\Interfaces\ThrowIfNotAbleInterface;
use App\Helpers\LengthAwarePaginator;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Common\Model\Backlog\Backlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class BacklogRepository
 * @package App\Http\Domain\Common\Repositories\Backlog
 */
class BacklogRepository implements BacklogRepositoryInterface, ThrowIfNotAbleInterface
{
    use ThrowIfNotAble;

    /** @var EloquentBacklog */
    private $eloquent_model;

    public function __construct()
    {
        $this->eloquent_model = EloquentBacklog::getModel();
    }

    /**
     * @param PaginateSearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(PaginateSearchRequest $request): LengthAwarePaginator
    {
        $query = $this->eloquent_model->newQuery()
            ->select(['id', 'user_id', 'school_id', 'work_div', 'work_status', 'reference', 'note', 'created_at', 'updated_at'])
            ->orderByDesc('created_at');
        /** @var \App\Http\Domain\Api\Requests\BacklogSearchRequest $request */
        if ($request->work_div) {
            $query = $query->where('work_div', $request->work_div);
        }
        if ($request->work_status) {
            $query = $query->where('work_status', $request->work_status);
        }

        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        $paginate->getCollection()->transform(function ($backlog) {
            return new Backlog($backlog);
        });

        return $paginate;
    }

    /**
     * @param int $id
     * @return Backlog|null
     * @throws \ReflectionException
     */
    public function getById(int $id): ?Backlog
    {
        $backlog = $this->eloquent_model->newQuery()->find($id);
        return $backlog ? new Backlog($backlog) : null;
    }

    /**
     * @param array $attribute
     * @return array|null
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    public function create(array $attribute): ?Backlog
    {
        return $this->createAble(EloquentBacklog::class, function () use ($attribute) {
            /** @var EloquentBacklog $backlog */
            $backlog = $this->eloquent_model->createOrFail($attribute);

            if ($backlog) {
                return new Backlog($backlog);
            }

            return null;
        });
    }

    /**
     * @param array $attributes
     * @return mixed
     * @throws \ReflectionException
     */
    public function push(array $attributes): mixed
    {
        return $this->createAble(EloquentBacklog::class, function () use ($attributes) {
            $key = uniqid('', true);
            $now = Carbon::now();
            $attributes = array_map(function ($attribute) use ($key, $now) {
                $attribute['created_at'] = $now;
                $attribute['note'] = $key;
                return $attribute;
            }, $attributes);

            if (!$this->eloquent_model->newQuery()->insert($attributes)) {
                return null;
            }

            $results = $this->eloquent_model->newQuery()->where('note', $key)->get();
            DB::statement('UPDATE backlogs SET "note"=NULL WHERE "note"='."'$key'");

            return $results->map(fn($backlog) => new Backlog($backlog));
        });
    }

    /**
     * @param int $id
     * @param array $attribute
     * @return bool
     */
    public function update(int $id, array $attribute): bool
    {
        /** @var EloquentBacklog $backlog */
        $backlog = $this->eloquent_model->newQuery()->findOrFail($id);
        return (bool)$backlog->update($attribute);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function delete(int $id): bool
    {
        return $this->deleteAble(EloquentBacklog::class, function () use ($id) {
            $backlog = $this->eloquent_model->newQuery()->findOrFail($id);
            return $backlog->deleteOrFail() ?? false;
        });
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getWait(array $ids): array
    {
        return EloquentBacklog::query()->whereIn('id', $ids)
            ->get(['id', 'work_div'])
            ->groupBy('work_div')
            ->map(function ($collect) {
                /** @var \Illuminate\Database\Eloquent\Collection $collect */
                return $collect->pluck('id');
            })
            ->toArray();

    }
}
