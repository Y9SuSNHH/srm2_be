<?php

namespace App\Http\Domain\Api\Repositories\Role;

use App\Eloquent\Role as EloquentRole;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Api\Requests\Role\CreateRequest;
use App\Http\Domain\Api\Requests\Role\UpdateRequest;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use App\Http\Enum\Role;

/**
 * Class RoleRepository
 * @package App\Http\Domain\Api\Repositories\Role
 */
class RoleRepository implements RoleRepositoryInterface
{
    use ThrowIfNotAble;

    /** @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model */
    private $eloquent_model;

    public function __construct()
    {
        $this->eloquent_model = EloquentRole::query()->getModel();
    }

    /**
     * @param BaseSearchRequest $request
     * @return array
     */
    public function getAll(BaseSearchRequest $request): array
    {
        if (!$request->keyword) {
            return $this->eloquent_model->newQuery()->get(['id', 'name', 'description'])->toArray();
        }

        return $this->eloquent_model->newQuery()
            ->orWhereILike('name', $request->getKeyword())
            ->orWhereILike('description', $request->getKeyword())
            ->get(['id', 'name', 'description'])->toArray();
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $role = $this->eloquent_model->with('permissions')->findOrFail($id);

        return $role ? $role->toArray() : null;
    }

    /**
     * @param CreateRequest $request
     * @return array
     */
    public function create(CreateRequest $request): array
    {
        return $this->createAble(EloquentRole::class, function () use ($request) {
            $attribute = [
                'name' => $request->name,
                'description' => $request->description,
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
        return $this->updateAble(EloquentRole::class, function () use ($id, $request) {
            $attribute = [
                'name' => $request->name,
                'description' => $request->description,
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
        return $this->deleteAble(EloquentRole::class, function () use ($id) {
            /** @var EloquentRole $role */
            $role = $this->eloquent_model->findOrFail($id);

            if (Role::ADMIN === $role->name) {
                throw new \Exception('Cannot deleted');
            }

            return (bool)$role->delete();
        });
    }

    /**
     * @param int $role_id
     * @param UpdateRequest $request
     * @return bool
     */
    public function assignUser(int $role_id, UpdateRequest $request): bool
    {
        return $this->updateAble([\App\Eloquent\User::class, EloquentRole::class], function () use ($role_id, $request) {
            if ($request->empty('users')) {
                throw new \Exception('No user selected');
            }

            /** @var EloquentRole $role */
            $role = $this->eloquent_model->newQuery()->findOrFail($role_id);
            return !empty($role->users()->sync($request->users));
        });
    }

    /**
     * @param int $role_id
     * @param UpdateRequest $request
     * @return bool
     */
    public function assignPermission(int $role_id, UpdateRequest $request): bool
    {
        return $this->updateAble([\App\Eloquent\Permission::class, EloquentRole::class], function () use ($role_id, $request) {
            if ($request->empty('permissions')) {
                throw new \Exception('No user selected');
            }

            /** @var EloquentRole $role */
            $role = $this->eloquent_model->newQuery()->findOrFail($role_id);
            $role->permissions()->sync($request->permissions);

            return true;
        });
    }
}
