<?php

namespace App\Http\Domain\Common\Repositories\StudentClassroom;

use Carbon\Carbon;

interface StudentClassroomRepositoryInterface
{
    /**
     * @param Carbon $ended
     * @param array $students
     * @param int|null $user_id
     * @return int
     */
    public function updateEnded(Carbon $ended, array $students, int $user_id = null): int;

    /**
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes): bool;
}
