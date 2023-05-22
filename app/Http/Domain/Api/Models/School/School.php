<?php

namespace App\Http\Domain\Api\Models\School;

use App\Helpers\Json;

class School extends Json
{
    public $id;
    public $school_code;
    public $school_name;
    public $school_status;
    public $service_name;
    public $priority;
    public $theme;
}
