<?php

namespace App\Http\Domain\Student\Models;

use App\Helpers\Json;

class Classroom extends Json
{
    public $id;
    public $code;
    public $area;
    public $enrollment_wave;

    public static function dates(): array
    {
        return [
            'enrollment_wave.first_day_of_school',
        ];
    }
}