<?php

namespace App\Http\Domain\Common\Repositories;

use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\LengthAwarePaginator;

/**
 * Interface BaseRepositoryInterface
 * @package App\Http\Domain\Common\Repositories
 */
interface BaseRepositoryInterface
{
    /**
     * @param PaginateSearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(PaginateSearchRequest $request): LengthAwarePaginator;

    /**
     * @param int $id
     * @return mixed
     */
    public function getById(int $id): mixed;

    /**
     * @param array $attribute
     * @return mixed
     */
    public function create(array $attribute): mixed;

    /**
     * @param int $id
     * @param array $attribute
     * @return mixed
     */
    public function update(int $id, array $attribute): mixed;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}