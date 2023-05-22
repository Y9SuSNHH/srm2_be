<?php

namespace App\Http\Domain\Student\Models\LearningModule;

use App\Eloquent\LearningModule as EloquentLearningModule;
use App\Helpers\Json;
use App\Http\Domain\Student\Models\Grade\Grade as GradeModel;
use App\Http\Enum\GradeDiv;

class LearningModule extends Json
{
    public $id;
    public $code;
    public $amount_credit;
    public $subject_name;
    public $exam_date;
    public $list_grade_div;
    public $grades;
    public $note;

    public function __construct(EloquentLearningModule $learning_module)
    {
        $subject_name   = $learning_module->subject->name;
        $grades         = $learning_module->grades;
        $list_grade_div = [];
        $exam_date      = null;
        $note           = '';
        foreach ($grades as $each) {
            $list_grade_div[$each->grade_div] = GradeDiv::from($each->grade_div)->getLang();
            $exam_date                        = $each->exam_date;
            if($each->note){
                if ($note) $note .= '<br>';
                $note .= $list_grade_div[$each->grade_div] . ' ' . $each->note;
            }
        }

        $grades->transform(function ($grade) {
            return new GradeModel($grade);
        });


        parent::__construct(array_merge($learning_module->toArray(), [
            'subject_name'   => $subject_name,
            'exam_date'      => $exam_date,
            'list_grade_div' => $list_grade_div,
            'grades'         => $grades,
            'note'           => $note,
        ]));
    }

    public static function dates(): array
    {
        return [
            'exam_date',
        ];
    }
}