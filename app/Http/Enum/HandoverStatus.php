<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

/**
 * Class HandoverStatus
 * @package App\Http\Enum
 */
class HandoverStatus extends Enum
{
    public const SEND_PROFILE_TO_SCHOOL = 1;
    public const SCHOOL_TAKE_PROFILE = 2;
    public const SCHOOL_SIGN_PROFILE = 3;
    public const SCHOOL_RETURN_SIGN_PROFILE = 4;
}
