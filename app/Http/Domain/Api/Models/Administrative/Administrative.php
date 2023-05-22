<?php

namespace App\Http\Domain\Api\Models\Administrative;

use App\Helpers\Json;

class Administrative extends Json
{
    public $id;
    public $school_code;
    public $school_name;
    public $school_status;
    public $service_name;
    public $priority;
    public $theme;
}
