<?php

namespace App\Http\Domain\Student\Models;

use App\Helpers\Json;
use App\Http\Enum\StudentStatus;
use ReflectionException;

class Content extends Json
{
    public $classromm;
    public $student_status;
    public $student_status_name;
    public $classroom_code;
    public $first_day_of_school;
    public $area;

    /**
     * @throws ReflectionException
     */
    public function __construct(object $content)
    {
        $student_status_name = $content->student_status ? StudentStatus::from($content->student_status)->getLang() : null;

        parent::__construct(array_merge((array)$content, [
            'student_status_name' => $student_status_name,
        ]));
    }

    public static function dates(): array
    {
        return [
            'first_day_of_school',
        ];
    }
}