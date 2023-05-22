<?php

namespace App\Http\Domain\TrainingProgramme\Models\LearningModule;

use App\Helpers\Json;
use App\Eloquent\LearningModule as EloquentLearningModule;

class LearningModule extends Json
{
    public $id;
    public $school_id;
    public $subject_id; 
    public $subject_name; 
    public $subject_code;
    public $code;
    public $amount_credit;
    public $alias;
    public $grade_setting_div;

    public function __construct(EloquentLearningModule $learning_module)
    {
        parent::__construct(array_merge($learning_module->toArray(), [
            'subject_name' => $learning_module->subject->name ?? $learning_module->alias ?? '',
            'subject_code' => $learning_module->subject->code ?? $learning_module->alias ?? '',

        ]));
    }
}