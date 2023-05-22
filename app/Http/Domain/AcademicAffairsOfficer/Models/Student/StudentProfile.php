<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Models\Student;

use App\Eloquent\StudentProfile as EloquentStudentProfile;
use App\Helpers\Json;

class StudentProfile extends Json
{
    public $id;
    public $profile_code;
    public $profile_id;
    public $documents;
    public $handover_id;
    public $profile;
    public $student;

    public function __construct(EloquentStudentProfile $student_profile)
    {
        $documents = new StudentProfileDocument($student_profile->documents);
        $student   = new Student($student_profile->student);
        parent::__construct(array_merge($student_profile->toArray(), [
            'documents' => $documents,
            'student'   => $student,
        ]));
    }

    public static function dates(): array
    {
        return [];
    }

}
