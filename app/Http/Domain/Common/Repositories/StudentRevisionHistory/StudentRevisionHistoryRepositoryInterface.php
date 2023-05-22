<?php

namespace App\Http\Domain\Common\Repositories\StudentRevisionHistory;

use Carbon\Carbon;

interface StudentRevisionHistoryRepositoryInterface
{
    /**
     * @param Carbon $ended
     * @param array $students
     * @param int $type
     * @param int|null $user_id
     * @return int
     */
    public function updateEnded(Carbon $ended, array $students, int $type, int $user_id = null): int;

    /**
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes): bool;
}
