<?php

namespace App\Http\Domain\Api\Repositories\Student;

interface GradeRepositoryInterface
{
    /**
     * @param string $student_code
     * @return mixed
     */
    public function getStudentGrades(string $student_code): mixed;
}
