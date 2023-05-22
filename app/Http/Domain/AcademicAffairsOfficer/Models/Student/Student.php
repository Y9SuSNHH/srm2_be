<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Models\Student;

use App\Eloquent\Student as EloquentStudent;
use App\Helpers\Json;
use App\Http\Enum\StudentStatus;

/**
 * Class Student
 * @package App\Http\Domain\Student\Models\Student
 *
 * @property StudentProfileDocument $documents
 */
class Student extends Json
{
    public $id;
    public $student_profile_id;
    public $classroom;
    public $before_student_status;
    public $before_student_status_name;

    public function __construct(EloquentStudent $student)
    {
//        dd($student->latestStudentRevisionHistory);
        $student_status_name = '';
        if ($student->student_status) {
            $student_status_name = StudentStatus::from($student->student_status)->getLang();
        }
        if ($student->latestStudentRevisionHistory) {
            $before_student_status      = (int)$student->latestStudentRevisionHistory->value;
            $before_student_status_name = StudentStatus::from($before_student_status)->getLang();
        }

        parent::__construct(array_merge($student->toArray(), [
            'before_student_status'      => $before_student_status ?? $student->student_status,
            'before_student_status_name' => $before_student_status_name ?? $student_status_name,
        ]));
    }

    public static function dates(): array
    {
        return [];
    }
}