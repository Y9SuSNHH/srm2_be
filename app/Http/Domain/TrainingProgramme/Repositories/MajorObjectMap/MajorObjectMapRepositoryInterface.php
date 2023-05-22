<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\MajorObjectMap;

use App\Http\Domain\TrainingProgramme\Requests\MajorObjectMap\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface MajorObjectMapRepositoryInterface
{
    /**
     * @param int $area_id
     * @return mixed
     */
    public function getMajorAndObject(int $area_id): mixed;

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
     * Get major_object_map by id
     * @param int $id
     * @return array
     */
    public function getById(int $id): array;

    /**
     * Create major_object_map
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array;

    // /**
    //  * Update major_object_map
    //  * @param array $validator
    //  * @param int $id
    //  * @return array
    //  */
    // public function update(int $id, array $validator): array;

    // /**
    //  * Delete major_object_map
    //  * @param int $id
    //  * @return array
    //  */
    // public function delete(int $id): array;

}