<?php

namespace App\Http\Domain\Common\Repositories\Backlog;

use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\LengthAwarePaginator;
use App\Http\Domain\Common\Model\Backlog\Backlog;
use App\Http\Domain\Common\Repositories\BaseRepositoryInterface;

interface BacklogRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param PaginateSearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(PaginateSearchRequest $request): LengthAwarePaginator;

    /**
     * @param int $id
     * @return Backlog|null
     */
    public function getById(int $id): ?Backlog;

    /**
     * @param array $attribute
     * @return Backlog|null
     */
    public function create(array $attribute): ?Backlog;

    /**
     * @param array $attributes
     * @return mixed
     */
    public function push(array $attributes): mixed;

    /**
     * @param int $id
     * @param array $attribute
     * @return bool
     */
    public function update(int $id, array $attribute): bool;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * @param array $ids
     * @return array
     */
    public function getWait(array $ids): array;
}
