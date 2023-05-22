<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Models\Grade;

use App\Eloquent\Grade as EloquentGrade;
use App\Helpers\Json;
use App\Http\Enum\GradeDiv;

class Grade extends Json
{
    public $learning_module_id;
    public $learning_module_name;
    public $learning_module_code;
    public $exam_date;
    public $storage_file_id;
    public $amount_credit;
    public $classroom;
    public $student_code;
    public $firstname;
    public $lastname;
    public $birthday;
    public $grades;
    public $note;
    public $count;

    /**
     * Grade constructor.
     * @param EloquentGrade $grade
     */
    public function __construct(EloquentGrade $grade)
    {
        $student = $grade->student;
        $this->student_code = $student?->student_code;
        $this->firstname = $student?->studentProfile?->profile?->firstname;
        $this->lastname = $student?->studentProfile?->profile?->lastname;
        $this->birthday = $student?->studentProfile?->profile?->birthday;
        $this->classroom = optional(optional($student?->classrooms)?->first())?->code;
        $this->learning_module_id = $grade->learning_module_id;
        $this->learning_module_name = $grade->learningModule?->subject?->name;
        $this->learning_module_code = $grade->learningModule?->code;
        $this->exam_date = $grade->exam_date;
        $this->amount_credit = $grade->learningModule?->amount_credit;
        $this->note = $grade->note;
        $this->storage_file_id = $grade->storage_file_id;
        $this->grades = [];
        $this->count = $grade->learningModule?->grade_setting_div;

        foreach ($grade->gradeValues as $value) {
            $this->grades[GradeDiv::fromOptional($value->grade_div)->getKey()] = $value->value;
        }
    }

    public static function dates(): array
    {
        return ['exam_date'];
    }
}
