<?php

namespace App\Http\Domain\TrainingProgramme\Models\StudyPlan;

use App\Helpers\Json;
use App\Eloquent\StudyPlan as EloquentStudyPlan;

class StudyPlan extends Json
{
    public $id;
    public $area_id;
    public $area_name;
    public $classroom_id;
    public $classroom_code;
    public $first_day_of_school;
    public $credit_price;
    public $major_id;
    public $major_shortcode;
    public $learning_module;
    public $subject;
    public $semester; 
    public $slot;
    public $learning_module_id;
    public $study_began_date;
    public $study_ended_date;
    public $day_of_the_test;

    public function __construct(EloquentStudyPlan $study_plan)
    {
        parent::__construct(array_merge($study_plan->toArray(), [
            'area_id' => $study_plan->classroom->area->id,
            'area_name' => $study_plan->classroom->area->name,
            'classroom_id' => $study_plan->classroom->id,
            'classroom_code' => $study_plan->classroom->code,
            'first_day_of_school' => $study_plan->classroom->enrollmentWave->first_day_of_school,
            'credit_price' => $study_plan->classroom->enrollmentWave->creditPrice->price,
            'major_id' => $study_plan->classroom->major->id,
            'major_shortcode' => $study_plan->classroom->major->shortcode,
            'learning_module' => $study_plan->learningModule,
            'subject' => $study_plan->subject,
        ]));
    }
}