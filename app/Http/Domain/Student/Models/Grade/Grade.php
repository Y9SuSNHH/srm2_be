<?php

namespace App\Http\Domain\Student\Models\Grade;

use App\Eloquent\Grade as EloquentGrade;
use App\Helpers\Json;
use App\Http\Enum\GradeDiv;

class Grade extends Json
{
    public $id;
    public $learning_module_id;
    public $exam_date;
    public $note;
    public $learning_module;
    public $grade_values;

    public function __construct(EloquentGrade $grade)
    {
        $grade_values = [];
        foreach ($grade->gradeValues as $value) {
            $grade_values[GradeDiv::fromOptional($value->grade_div)->getKey()] = $value->value;
        }
        parent::__construct(array_merge($grade->toArray(), [
            'grade_values' => $grade_values
        ]));
        // foreach ($grade->gradeValues as $each) {
        //     $each->grade_div_name = GradeDiv::from($each->grade_div)->getLang();
        //     $this->grade_values[GradeDiv::fromOptional($each->grade_div)->getKey()] = $each->value;
        // }
        // parent::__construct(array_merge($grade->toArray(), []));
    }

    public static function dates(): array
    {
        return [
            'exam_date',
        ];
    }
}