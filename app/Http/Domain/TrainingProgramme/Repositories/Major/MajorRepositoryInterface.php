<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Major;

use App\Http\Domain\TrainingProgramme\Requests\Major\CreateMajorRequest;

interface MajorRepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll(): array;

    /**
     * Get major by id
     * @param int $id
     * @return array
     */
    public function getById(int $id): array;

    /**
     * Create major
     * @param CreateMajorRequest $request
     * @return array
     */
    public function create(CreateMajorRequest $request): array;

    /**
     * Update major
     * @param CreateMajorRequest $request
     * @param int $id
     * @return array
     */
    public function update(int $id, CreateMajorRequest $request): array;

    /**
     * Delete major
     * @param int $id
     * @return array
     */
    public function delete(int $id): bool;

    public function findMajorByShortcodes(array $names);
}