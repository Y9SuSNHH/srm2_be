<?php

namespace App\Http\Domain\TrainingProgramme\Models\EnrollmentObject;

use App\Helpers\Json;

class EnrollmentObject extends Json
{
    public $id;
    public $school_id;
    public $code;
    public $classification;
    public $name;
    public $shortcode;
}