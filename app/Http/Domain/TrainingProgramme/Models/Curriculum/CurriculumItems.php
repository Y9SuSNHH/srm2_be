<?php

namespace App\Http\Domain\TrainingProgramme\Models\Curriculum;

use App\Helpers\Json;

/**
 * Class CurriculumItems
 * @package App\Http\Domain\TrainingProgramme\Models\Curriculum
 */
class CurriculumItems extends Json
{
    public $id;
    public $training_program_id;
    public $learning_module_id;
    public $enrollment_object_id;
    public $subject_id;
}