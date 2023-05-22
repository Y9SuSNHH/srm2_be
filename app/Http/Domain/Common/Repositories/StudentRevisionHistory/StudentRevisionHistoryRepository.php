<?php

namespace App\Http\Domain\Common\Repositories\StudentRevisionHistory;

use App\Eloquent\StudentRevisionHistory;
use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\LengthAwarePaginator;
use Carbon\Carbon;

class StudentRevisionHistoryRepository implements StudentRevisionHistoryRepositoryInterface
{

    public function getAll(PaginateSearchRequest $request): LengthAwarePaginator
    {
        // TODO: Implement getAll() method.
    }

    public function getById(int $id): mixed
    {
        // TODO: Implement getById() method.
    }

    public function create(array $attribute): mixed
    {
        // TODO: Implement create() method.
    }

    public function update(int $id, array $attribute): mixed
    {
        // TODO: Implement update() method.
    }

    /**
     * @param Carbon $ended
     * @param array $students
     * @param int $type
     * @param int|null $user_id
     * @return int
     */
    public function updateEnded(Carbon $ended, array $students, int $type, int $user_id = null): int
    {
        $query = StudentRevisionHistory::query()->whereNull('ended_at')->where('type', $type)->whereIn('student_id', $students);
        return $query->update(['ended_at' => $ended, 'updated_at' => Carbon::now(), 'updated_by' => $user_id ?? auth()->getId()]);
    }

    /**
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes): bool
    {
        return StudentRevisionHistory::query()->insert($attributes);
    }
}
