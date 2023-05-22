<?php

namespace App\Http\Domain\Common\Repositories\StudentClassroom;

use App\Eloquent\StudentClassroom;
use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\LengthAwarePaginator;
use Carbon\Carbon;

class StudentClassroomRepository implements StudentClassroomRepositoryInterface
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

    public function delete(int $id): bool
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param Carbon $ended
     * @param array $students
     * @param int|null $user_id
     * @return int
     */
    public function updateEnded(Carbon $ended, array $students, int $user_id = null): int
    {
        $query = StudentClassroom::query()->whereNull('ended_at')->whereIn('student_id', $students);
        return $query->update(['ended_at' => $ended, 'updated_at' => Carbon::now(), 'updated_by' => $user_id ?? auth()->getId()]);
    }

    /**
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes): bool
    {
        return StudentClassroom::query()->insert($attributes);
    }
}
