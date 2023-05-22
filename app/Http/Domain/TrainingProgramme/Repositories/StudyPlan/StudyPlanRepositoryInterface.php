<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\StudyPlan;

use App\Http\Domain\TrainingProgramme\Requests\StudyPlan\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface StudyPlanRepositoryInterface
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
     * Get study_plan by id
     * @param int $id
     * @return array
     */
    public function getById(int $id): array;

    /**
     * Create study_plan
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array;

    /**
     * Update study_plan
     * @param array $validator
     * @param int $id
     * @return array
     */
    public function update(int $id, array $validator): array;

    /**
     * Delete study_plan
     * @param int $id
     * @return array
     */
    public function delete(int $id): array;

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function export(SearchRequest $request): mixed;

    /**
     * @return array
     */
    public function getListPrice(): array;

    /**
     * @return array
     */
    public function getCodeAndAccount(): array;
}
