<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom;

use App\Http\Domain\AcademicAffairsOfficer\Models\Classroom\Classroom;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom\SearchRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ClassRepositoryInterface
{
    /**
     * @param array $ids
     * @return Collection
     */
    public function findAll(array $ids): Collection;

    /**
     * Get all
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator;

    /**
     * @param SearchRequest $request
     * @param array $columns
     * @return mixed
     */
    public function options(SearchRequest $request, array $columns = ['*']): mixed;

    /**
     * Get by id
     * @param int $id
     * @return Classroom
     */
    public function getById(int $id): Classroom;

    /**
     * Create
     *
     * @param array $attribute
     * @return Classroom
     */
    public function create(array $attribute): Classroom;

    /**
     * @param int $id
     * @param array $attribute
     * @return Classroom
     */
    public function update(int $id, array $attribute): Classroom;

    /**
     * Delete
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes): bool;

    /**
     * @param array $ids
     * @param array $attribute
     * @return bool|int
     */
    public function updateMultipleRecords(array $ids, array $attribute): bool|int;

    /**
     * @param int $area_id
     * @return array
     */
    public function getCodeExists(int $area_id): array;
}
