<?php

namespace App\Http\Domain\Student\Repositories\StudentClassroom;

use Illuminate\Support\Collection;

interface StudentClassroomRepositoryInterface
{
    /**
     * @param int $student_id
     * @return Collection
     */
    public function fetchByDate(int $student_id): Collection;
}
