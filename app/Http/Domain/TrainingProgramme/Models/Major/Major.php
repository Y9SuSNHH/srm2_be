<?php

namespace App\Http\Domain\TrainingProgramme\Models\Major;

use App\Helpers\Json;

class Major extends Json
{
    public $id;
    public $school_id;
    public $area_id;
    public $code;
    public $name;
    public $shortcode;
}