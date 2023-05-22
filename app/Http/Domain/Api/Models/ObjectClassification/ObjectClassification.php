<?php

namespace App\Http\Domain\Api\Models\ObjectClassification;

use App\Helpers\Json;

class ObjectClassification extends Json
{
    public $id;
    public $school_id;
    public $name;
    public $abbreviation;
    public $description;
}