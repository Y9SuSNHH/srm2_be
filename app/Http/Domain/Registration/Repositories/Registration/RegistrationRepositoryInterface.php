<?php

namespace App\Http\Domain\Registration\Repositories\Registration;

use App\Http\Domain\Registration\Requests\Registration\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface RegistrationRepositoryInterface
{
    /**
     * Get all
     * @return \Illuminate\Support\Collection
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator;

    /**
     * Update Registration
     * @param array $validator
     * @param int $id
     * @return array
     */
    public function update(int $id, array $validator): array;

    /**
     * Delete Registration
     * @param int $id
     * @return array
     */
    public function delete(int $id): array;

    /**
     * Info Registration
     * @param int $id
     * @return array
     */
    public function getById(int $id): array;

}
