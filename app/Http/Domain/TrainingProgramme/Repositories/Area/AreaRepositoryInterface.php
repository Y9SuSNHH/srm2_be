<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Area;

use App\Http\Domain\TrainingProgramme\Models\Area\Area as AreaModel;
use App\Http\Domain\TrainingProgramme\Requests\Area\CreateAreaRequest;
use App\Http\Domain\TrainingProgramme\Requests\Area\UpdateAreaRequest;

interface AreaRepositoryInterface
{
    /**
     * Get all
     * @return \Illuminate\Support\Collection|\Illuminate\Support\Enumerable
     */
    public function getAll();

    /**
     * Get staff by id
     * @param int $id
     * @return AreaModel
     */
    public function getById(int $id): AreaModel;

    /**
     * Create area
     * @param CreateAreaRequest $request
     * @return AreaModel
     */
    public function create(CreateAreaRequest $request): AreaModel;

    /**
     * Update area
     * @param int $id
     * @param UpdateAreaRequest $request
     * @return AreaModel
     */
    public function update(int $id, UpdateAreaRequest $request): AreaModel;

    /**
     * Delete area
     * @param int $id
     * @return array
     */
    public function delete(int $id): array;
}
