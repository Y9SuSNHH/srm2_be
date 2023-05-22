<?php

namespace App\Http\Domain\Student\Repositories\StudentRevisionHistory;

use Illuminate\Support\Collection;

interface StudentRevisionHistoryRepositoryInterface
{
    /**
     * @param int $student_id
     * @return Collection
     */
    public function fetchStudentStatusByDate(int $student_id): Collection;
}
