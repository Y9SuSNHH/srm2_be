<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class StudentTypeWeek extends Enum
{
    public const A1 = 0;

    public const A2 = 3;

    public const B1 = 4;

    public const B2 = 5;

    public const B2_HS = 6;

    public const B2_HT = 7;

    public const B3 = 8;

    public const C = 9;

    public static function qualifiedStudents()
    {
        return [StudentTypeWeek::A1,StudentTypeWeek::A2];
    }

}