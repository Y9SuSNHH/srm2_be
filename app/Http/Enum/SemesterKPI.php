<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class SemesterKPI extends Enum
{
    public const KY_1 = [
        'tong' => 100,
        'tuan_1' => 100,
        'tuan_2' => 100,
        'tuan_3' => 100,
        'tuan_4' => 100,
        'tuan_5' => 100,
        'tuan_6' => 100,
    ];

    public const KY_2 = [
        'tong' => 84,
        'tuan_1' => 8,
        'tuan_2' => 25,
        'tuan_3' => 30,
        'tuan_4' => 30,
        'tuan_5' => 5,
        'tuan_6' => 2,
    ];

    public const KY_2_NNA = [
        'tong' => 80,
        'tuan_1' => 8,
        'tuan_2' => 25,
        'tuan_3' => 30,
        'tuan_4' => 30,
        'tuan_5' => 5,
        'tuan_6' => 2,
    ];

    public const KY_3 = [
        'tong' => 88,
        'tuan_1' => 8,
        'tuan_2' => 25,
        'tuan_3' => 30,
        'tuan_4' => 30,
        'tuan_5' => 5,
        'tuan_6' => 2,
    ];
    
    public const KY_3_NNA = [
        'tong' => 84,
        'tuan_1' => 8,
        'tuan_2' => 25,
        'tuan_3' => 30,
        'tuan_4' => 30,
        'tuan_5' => 5,
        'tuan_6' => 2,
    ];
    
    public const KY_4 = [
        'tong' => 94,
        'tuan_1' => 8,
        'tuan_2' => 25,
        'tuan_3' => 30,
        'tuan_4' => 30,
        'tuan_5' => 5,
        'tuan_6' => 2,
    ];

    public const KY_5 = [
        'tong' => 96,
        'tuan_1' => 8,
        'tuan_2' => 25,
        'tuan_3' => 30,
        'tuan_4' => 30,
        'tuan_5' => 5,
        'tuan_6' => 2,
    ];

    public const KY_6 = [
        'tong' => 97,
        'tuan_1' => 8,
        'tuan_2' => 25,
        'tuan_3' => 30,
        'tuan_4' => 30,
        'tuan_5' => 5,
        'tuan_6' => 2,
    ];
    
    public const KY_7 = [
        'tong' => 97,
        'tuan_1' => 8,
        'tuan_2' => 25,
        'tuan_3' => 30,
        'tuan_4' => 30,
        'tuan_5' => 5,
        'tuan_6' => 2,
    ];

    public const KY_8 = [
        'tong' => 97,
        'tuan_1' => 8,
        'tuan_2' => 25,
        'tuan_3' => 30,
        'tuan_4' => 30,
        'tuan_5' => 5,
        'tuan_6' => 2,
    ];

    public const KY_9 = [
        'tong' => 97,
        'tuan_1' => 8,
        'tuan_2' => 25,
        'tuan_3' => 30,
        'tuan_4' => 30,
        'tuan_5' => 5,
        'tuan_6' => 2,
    ];

    public static function P845AWeeks() : array
    {
        return [
            'tuan_0',
            'tuan_1',
            'tuan_2',
            'tuan_3',
            'tuan_4',
            'tuan_5',
            'tuan_6',
        ];
    }
}