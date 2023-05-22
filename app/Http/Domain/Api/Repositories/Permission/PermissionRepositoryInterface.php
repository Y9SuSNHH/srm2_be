<?php

namespace App\Http\Domain\Api\Repositories\Permission;

use App\Http\Domain\Api\Requests\Permission\CreateRequest;
use App\Http\Domain\Api\Requests\Permission\UpdateRequest;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface PermissionRepositoryInterface
{
    /**
     * @param BaseSearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(BaseSearchRequest $request): LengthAwarePaginator;

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
     * @param int $permission_id
     * @param UpdateRequest $request
     * @return bool
     */
    public function assignRole(int $permission_id, UpdateRequest $request): bool;

    /**
     * @param int $permission_id
     * @param UpdateRequest $request
     * @return bool
     */
    public function assignUser(int $permission_id, UpdateRequest $request): bool;
}
