<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class AdmissionObject extends Enum
{
    public const  HIGHSCHOOL = 1;

    public const  INTERMEDIATE = 2;

    public const  COLLEGE = 3;

    public const  UNIVERSITY = 4;

    public static function defineLang($base, $key): string
    {
        $lang = ['admission_object' => [
            'HIGHSCHOOL' => 'THPT',
            'INTERMEDIATE' => 'TC',
            'COLLEGE' => 'CĐ',
            'UNIVERSITY' => 'VB2',
        ]];

        return $lang[$base][$key];
    }
}