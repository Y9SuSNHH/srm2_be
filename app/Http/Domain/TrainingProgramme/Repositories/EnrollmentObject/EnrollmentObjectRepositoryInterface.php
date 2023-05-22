<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\EnrollmentObject;

use App\Http\Domain\TrainingProgramme\Requests\EnrollmentObject\CreateEnrollmentObjectRequest;
use App\Http\Domain\TrainingProgramme\Requests\EnrollmentObject\SearchRequest;

interface EnrollmentObjectRepositoryInterface
{
    /**
     * Get all
     * @param SearchRequest $request
     * @return array
     */
    public function getAll(SearchRequest $request): array;

    /**
     * @param SearchRequest $request
     * @return array
     */
    public function getOptions(SearchRequest $request): array;

    /**
     * Get enrollment object by id
     * @param int $id
     * @return array
     */
    public function getById(int $id): array;

    /**
     * Create enrollment object
     * @param CreateEnrollmentObjectRequest $request
     * @return array
     */
    public function create(CreateEnrollmentObjectRequest $request): array;

    /**
     * Update enrollment object
     * @param CreateEnrollmentObjectRequest $request
     * @param int $id
     * @return array
     */
    public function update(int $id, CreateEnrollmentObjectRequest $request): array;

    /**
     * Delete enrollment object
     * @param int $id
     * @return array
     */
    public function delete(int $id): array;

    public function findEnrollmentObjectByShortcodes(array $codes);
}