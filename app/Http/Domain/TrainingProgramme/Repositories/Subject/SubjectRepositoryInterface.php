<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Subject;

use App\Http\Domain\TrainingProgramme\Requests\Subject\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface SubjectRepositoryInterface
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
     * Get subject by id
     * @param int $id
     * @return array
     */
    public function getById(int $id): array;

    /**
     * Create subject
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array;

    /**
     * Update subject
     * @param array $validator
     * @param int $id
     * @return array
     */
    public function update(int $id, array $validator): array;

    /**
     * Delete subject
     * @param int $id
     * @return array
     */
    public function delete(int $id): array;

}