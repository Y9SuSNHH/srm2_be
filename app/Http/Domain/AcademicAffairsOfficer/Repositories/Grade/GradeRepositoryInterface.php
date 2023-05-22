<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\Grade;

use App\Helpers\LengthAwarePaginator;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Grade\SearchRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface GradeRepositoryInterface
{
    /**
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator;

    /**
     * @param int $learning_module_id
     * @return Collection
     */
    public function getSetting(int $learning_module_id): Collection;

    /**
     * @param int $learning_module_id
     * @param Carbon $exam_date
     * @return Collection
     */
    public function getGradeExists(int $learning_module_id, Carbon $exam_date): Collection;

    /**
     * @param array $attributes
     * @param int|null $storage_file_id
     * @return array|null
     */
    public function insertGrade(array $attributes, int $storage_file_id = null): ?array;

    /**
     * @param array $attributes
     * @return bool
     */
    public function insertGradeValue(array $attributes): bool;

    /**
     * @param int $storage_file_id
     * @return array
     */
    public function getGradeDeleted(int $storage_file_id): array;

    /**
     * @param int $storage_file_id
     * @param array $grade_ids
     * @return bool
     */
    public function delete(int $storage_file_id, array $grade_ids): bool;
}
