<?php

namespace App\Http\Domain\Api\Repositories\Permission;

use App\Eloquent\Permission as EloquentPermission;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Api\Requests\Permission\CreateRequest;
use App\Http\Domain\Api\Requests\Permission\UpdateRequest;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class PermissionRepository
 * @package App\Http\Domain\Api\Repositories\Permission
 */
class PermissionRepository implements PermissionRepositoryInterface
{
    use ThrowIfNotAble;

    /** @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model */
    private $eloquent_model;

    /**
     * PermissionRepository constructor.
     */
    public function __construct()
    {
        $this->eloquent_model = EloquentPermission::query()->getModel();
    }

    /**
     * @param BaseSearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(BaseSearchRequest $request): LengthAwarePaginator
    {
        $query = $this->eloquent_model->newQuery();

        if ($request->keyword) {
            $query->orWhereILike('guard', $request->getKeyword());
            $query->orWhereILike('constraint', $request->getKeyword());
        }

        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        $paginate->getCollection()->transform(function ($permission) {
            /** @var EloquentPermission $permission */
            return [
                'id' => $permission->id,
                'guard' => $permission->guard,
                'action' => $permission->action,
                'constraint' => $permission->constraint,
            ];
        });

        return $paginate;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->eloquent_model
            ->findOrFail($id)
            ->toArray();
    }

    /**
     * @param CreateRequest $request
     * @return array
     */
    public function create(CreateRequest $request): array
    {
        return $this->createAble(EloquentPermission::class, function () use ($request) {
            $attribute = [
                'guard' => $request->guard,
                'action' => $request->action,
                'constraint' => $request->constraint,
                'created_by' => auth()->getId(),
            ];

            return $this->eloquent_model->create($attribute)->toArray();
        });
    }

    /**
     * @param int $id
     * @param UpdateRequest $request
     * @return bool
     */
    public function update(int $id, UpdateRequest $request): bool
    {
        return $this->updateAble(EloquentPermission::class, function () use ($id, $request) {
            $attribute = [
                'guard' => $request->guard,
                'action' => $request->action,
                'constraint' => $request->constraint,
                'updated_by' => auth()->getId(),
            ];

            return $this->eloquent_model->newQuery()->findOrFail($id)->updateOrFail($attribute);
        });
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->deleteAble(EloquentPermission::class, function () use ($id) {
            return (bool)$this->eloquent_model->findOrFail($id)->delete();
        });
    }

    /**
     * @param int $permission_id
     * @param UpdateRequest $request
     * @return bool
     */
    public function assignUser(int $permission_id, UpdateRequest $request): bool
    {
        return $this->updateAble([\App\Eloquent\User::class, EloquentPermission::class], function () use ($permission_id, $request) {
            if ($request->empty('users')) {
                throw new \Exception('No user selected');
            }

            /** @var EloquentPermission $permission */
            $permission = $this->eloquent_model->newQuery()->findOrFail($permission_id);
            return !empty($permission->users()->sync($request->users));
        });
    }

    /**
     * @param int $permission_id
     * @param UpdateRequest $request
     * @return bool
     */
    public function assignRole(int $permission_id, UpdateRequest $request): bool
    {
        return $this->updateAble([\App\Eloquent\Role::class, EloquentPermission::class], function () use ($permission_id, $request) {
            if ($request->empty('users')) {
                throw new \Exception('No user selected');
            }

            /** @var EloquentPermission $permission */
            $permission = $this->eloquent_model->newQuery()->findOrFail($permission_id);
            return !empty($permission->roles()->sync($request->roles));
        });
    }
}
