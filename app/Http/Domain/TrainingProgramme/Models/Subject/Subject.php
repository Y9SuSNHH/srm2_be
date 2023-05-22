<?php

namespace App\Http\Domain\TrainingProgramme\Models\Subject;

use App\Helpers\Json;

class Subject extends Json
{
    public $id;
    public $school_id;
    public $code;
    public $name; 
    public $description;
}