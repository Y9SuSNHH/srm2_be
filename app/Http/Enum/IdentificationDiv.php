<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

/**
 * Class IdentificationDiv
 * @package App\Http\Enum
 */
class IdentificationDiv extends Enum
{
    /**
     * has identification
     */
    public const AVAILABLE = 1;

    /**
     * not has identification, random value
     */
    public const UNAVAILABLE = 2;
}
