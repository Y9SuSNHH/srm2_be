<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class Deegree extends Enum
{
    public const  HIGHSCHOOL = 1;

    public const  INTERMEDIATE = 2;

    public const  COLLEGE = 3;

    public const  UNIVERSITY = 4;

    public static function defineLang($base, $key): string
    {
        $lang = ['deegree' => [
            'HIGHSCHOOL' => 'THPT',
            'INTERMEDIATE' => 'TC',
            'COLLEGE' => 'CĐ',
            'UNIVERSITY' => 'ĐH',
        ]];

        return $lang[$base][$key];
    }
}