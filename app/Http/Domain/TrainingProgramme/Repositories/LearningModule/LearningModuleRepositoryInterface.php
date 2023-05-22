<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\LearningModule;

use App\Http\Domain\TrainingProgramme\Requests\LearningModule\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface LearningModuleRepositoryInterface
{
    /**
     * Get all
     * @return \Illuminate\Support\Collection
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator;

    /**
     * @param SearchRequest|null $request
     * @return mixed
     */
    public function options(SearchRequest $request = null): mixed;
    
    /**
     * Get learning_module by id
     * @param int $id
     * @return array
     */
    public function getById(int $id): array;

    /**
     * Create learning_module
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array;

    /**
     * Update learning_module
     * @param array $validator
     * @param int $id
     * @return array
     */
    public function update(int $id, array $validator): array;

    /**
     * Delete learning_module
     * @param int $id
     * @return array
     */
    public function delete(int $id): array;
      
    /**
     * getListItems
     *
     * @return array
     */
    public function getListItems(): array;
}