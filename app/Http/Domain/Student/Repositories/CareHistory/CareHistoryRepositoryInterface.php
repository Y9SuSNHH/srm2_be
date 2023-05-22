<?php

namespace App\Http\Domain\Student\Repositories\CareHistory;

use App\Http\Domain\Student\Requests\CareHistory\SearchRequest;

interface CareHistoryRepositoryInterface
{
    /**
     * Get all
     * @return \Illuminate\Support\Collection
     */
    public function getAll(SearchRequest $request);

    /**
     * Create care history
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array;

    /**
     * Update care history
     * @param array $validator
     * @param int $id
     * @return array
     */
    public function update(int $id, array $validator): array;
}
