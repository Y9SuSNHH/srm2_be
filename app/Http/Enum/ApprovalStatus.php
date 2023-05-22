<?php

namespace App\Http\Enum;

use App\Helpers\Enum;
use App\Helpers\Traits\EnumBitwise;

class ApprovalStatus extends Enum
{
    use EnumBitwise;

    public const REJECT     = 1;
    public const ACCEPT     = 2;
    public const CLOSE      = 4;
    public const SENDING    = 8;
    public const IN_PROCESS = 16;
    public const PENDING    = 32;
    public const DONE       = 64;
}