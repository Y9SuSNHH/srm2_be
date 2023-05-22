<?php

namespace App\Http\Domain\Student\Repositories\StudentRevisionHistory;

use App\Http\Enum\ReferenceType;
use App\Http\Enum\StudentRevisionHistoryType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentRevisionHistoryRepository implements StudentRevisionHistoryRepositoryInterface
{
    /**
     * @param int $student_id
     * @return Collection
     */
    public function fetchStudentStatusByDate(int $student_id): Collection
    {
        $type = StudentRevisionHistoryType::STUDENT_STATUS;
        $reference_type = ReferenceType::PETITION;

        $sql = <<<SQL
            SELECT began_date, value, began_at, p.no, p.effective_date
            FROM (
              SELECT began_date, began_at , value, reference_type, reference_id,
                     row_number() over (partition by began_date ORDER BY began_at DESC) AS rn
              FROM student_revision_histories 
              WHERE student_id = $student_id AND "type" = $type
            ) srh
            LEFT JOIN petitions p on p.id = reference_id AND reference_type = $reference_type
            WHERE rn = 1
            ORDER BY began_date;
        SQL;
        return collect(DB::select($sql));
    }
}
