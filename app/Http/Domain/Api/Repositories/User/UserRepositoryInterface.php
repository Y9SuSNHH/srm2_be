<?php

namespace App\Http\Domain\Api\Repositories\User;

use App\Http\Domain\Api\Models\Staff\Staff as StaffModel;
use App\Http\Domain\Common\Requests\BaseSearchRequest;

interface UserRepositoryInterface
{
    /**
     * @param BaseSearchRequest $request
     * @return array
     */
    public function getAll(BaseSearchRequest $request): array;

    /**
     * @param int $user_id
     * @return StaffModel|null
     */
    public function getStaff(int $user_id): ?StaffModel;

    /**
     * @param array $attribute
     * @return array
     */
    public function create(array $attribute): array;

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
}
