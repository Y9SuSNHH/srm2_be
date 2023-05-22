<?php

namespace App\Http\Domain\Student\Repositories\StudentClassroom;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentClassroomRepository implements StudentClassroomRepositoryInterface
{
    /**
     * @param int $student_id
     * @return Collection
     */
    public function fetchByDate(int $student_id): Collection
    {
        $sql = <<<SQL
            SELECT began_date, classroom_id, began_at
            FROM (
              SELECT began_date, began_at , classroom_id, 
                     row_number() over (partition BY began_date ORDER BY began_at DESC) AS rn
              FROM student_classrooms 
              WHERE student_id = $student_id
            ) srh
            WHERE rn = 1
            ORDER BY began_date;
        SQL;
        return collect(DB::select($sql));
    }
}
