<?php

namespace App\Http\Domain\Api\Repositories\Role;

use App\Http\Domain\Api\Requests\Role\CreateRequest;
use App\Http\Domain\Api\Requests\Role\UpdateRequest;
use App\Http\Domain\Common\Requests\BaseSearchRequest;

/**
 * Interface RoleRepositoryInterface
 * @package App\Http\Domain\Api\Repositories\Role
 */
interface RoleRepositoryInterface
{
    /**
     * @param BaseSearchRequest $request
     * @return array
     */
    public function getAll(BaseSearchRequest $request): array;

    /**
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array;

    /**
     * @param CreateRequest $request
     * @return array
     */
    public function create(CreateRequest $request): array;

    /**
     * @param int $id
     * @param UpdateRequest $request
     * @return bool
     */
    public function update(int $id, UpdateRequest $request): bool;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * @param int $role_id
     * @param UpdateRequest $request
     * @return bool
     */
    public function assignUser(int $role_id, UpdateRequest $request): bool;

    /**
     * @param int $role_id
     * @param UpdateRequest $request
     * @return bool
     */
    public function assignPermission(int $role_id, UpdateRequest $request): bool;
}
