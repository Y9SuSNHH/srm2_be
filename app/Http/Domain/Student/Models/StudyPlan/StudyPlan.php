<?php

namespace App\Http\Domain\Student\Models\StudyPlan;

use App\Eloquent\StudyPlan as EloquentStudyPlan;
use App\Helpers\Json;

class StudyPlan extends Json
{
    public $id;
    public $day_of_the_test;
    public function __construct(EloquentStudyPlan $study_plan)
    {
        parent::__construct(array_merge($study_plan->toArray(), []));
    }

    public static function dates(): array
    {
        return [];
    }
}