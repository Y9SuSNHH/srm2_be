<?php

namespace App\Http\Domain\TrainingProgramme\Models\Curriculum;

use App\Helpers\Json;

/**
 * Class Curriculum
 * @package App\Http\Domain\TrainingProgramme\Models\Curriculum
 */
class Curriculum extends Json
{
    public $id;
    public $school_id;
    public $major_id;
    public $began_date;
}