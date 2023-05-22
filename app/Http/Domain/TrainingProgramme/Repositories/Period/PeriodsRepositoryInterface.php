<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Period;

use App\Http\Domain\TrainingProgramme\Requests\Period\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface PeriodsRepositoryInterface
{
    /**
     * Get all
     * @return \Illuminate\Support\Collection
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator;

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function options(SearchRequest $request): mixed;

    /**
     * Delete study_plan
     * @param int $id
     * @return array
     */
    public function delete(int $id): array;
}
