<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Curriculum;

use App\Http\Domain\TrainingProgramme\Requests\Curriculum\SearchRequest;
use App\Http\Domain\TrainingProgramme\Requests\Curriculum\CreateCurriculumRequest;
use App\Http\Domain\TrainingProgramme\Requests\Curriculum\EditCurriculumRequest;

interface CurriculumRepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll(SearchRequest $request);

    /**
     * Get major by id
     * @param int $id
     * @return array
     */
    public function getById(int $id): array;

    /**
     * Create major
     * @return array
     */
    public function create($request,$repository): array;

    /**
     * Update curriculum
     * @param EditCurriculumRequest $request
     * @param int $id
     * @return array
     */
    public function update(int $id, EditCurriculumRequest $request): array;

    /**
     * Delete curriculum
     * @return array
     */
    public function delete($id): array;

    public function getListItems($curriculum_ids): array;

    public function getMajorObjects(array $major_id): array;

    public function getLearningModule(array $ids);


}