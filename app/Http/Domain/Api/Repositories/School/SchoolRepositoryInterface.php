<?php

namespace App\Http\Domain\Api\Repositories\School;
use App\Http\Domain\Api\Requests\School\CreateSchoolRequest;
use App\Http\Domain\Api\Requests\School\UpdateSchoolRequest;
use App\Http\Domain\Api\Models\School\School;

interface SchoolRepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll(): array;

    /**
     * Get school by id
     * @param int $id
     * @return School
     */
    public function getById(int $id): School;

    /**
     * Create school
     * @param CreateSchoolRequest $request
     * @return School
     */
    public function create(CreateSchoolRequest $request): School;

    /**
     * Update school
     * @param int $id
     * @param UpdateSchoolRequest $request
     * @return School
     */
    public function update(int $id, UpdateSchoolRequest $request): School;

    /**
     * Delete school
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
