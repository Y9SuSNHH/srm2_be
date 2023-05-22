<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class PerPage extends Enum
{
    public const  TINY = 5;

    public const  SMALL = 10;

    public const  MEDIUM = 25;

    public const  LARGE = 50;

    public const  BIG = 100;

    /**
     * @return int
     */
    public static function getDefault(): int
    {
        return self::SMALL;
    }
}