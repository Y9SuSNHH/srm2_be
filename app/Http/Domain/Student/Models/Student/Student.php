<?php

namespace App\Http\Domain\Student\Models\Student;

use App\Eloquent\Student as EloquentStudent;
use App\Helpers\Json;
use App\Http\Enum\ProfileStatus;
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
    public $profile_code;
    public $student_code;
    public $fullname;
    public $email;
    public $birthday;
    public $gender;
    public $phone_number;
    public $classroom;
    public $account;
    public $student_status;
    public $student_status_name;
    public $profile_status;
    public $profile_status_name;
    public $student_profile;
    public $school;
    public $documents;
    public $curriculum_vitae;
    public $note;
    public $gop_khai_giang;

    public function __construct(EloquentStudent $student)
    {
        $fullname            = $student->studentProfile->profile->firstname . ' ' . $student->studentProfile->profile->lastname;
        $profile_code        = $student->studentProfile->profile_code;
        $birthday            = $student->studentProfile->profile->birthday;
        $gender              = $student->studentProfile->profile->gender ? 'Nữ' : 'Nam';
        $phone_number        = $student->studentProfile->profile->phone_number;
        $student_status_name = StudentStatus::from($student->student_status)->getLang();
        $profile_status_name = !$student->profile_status ? '' : ProfileStatus::from((int)$student->profile_status)->getKey();
        $documents           = new StudentProfileDocument($student->studentProfile->documents ?? '');// json_decode($student->studentProfile->documents ?? '');
        $curriculum_vitae    = new ProfileCurriculumVitae($student->studentProfile->profile->curriculum_vitae ?? '');
//        $gop_khai_giang   = optional($student->oldestClassrooms->first())->first_day_of_school;

//        dd($student->classroom->staff->toArray());
        parent::__construct(array_merge($student->toArray(), [
            'fullname'             => $fullname,
            'profile_code'         => $profile_code,
            'birthday'             => $birthday,
            'gender'               => $gender,
            'phone_number'         => $phone_number,
            'student_status_name'  => $student_status_name,
            'profile_status_name' => $profile_status_name,
            'documents'            => $documents,
            'curriculum_vitae'     => $curriculum_vitae,
        ]));
    }

    public static function dates(): array
    {
        return [
            'first_day_of_school',
            'birthday',
            'gop_khai_giang',
        ];
    }
}