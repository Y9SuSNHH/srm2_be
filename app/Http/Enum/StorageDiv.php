<?php

namespace App\Http\Enum;

use App\Helpers\Enum;
use App\Helpers\Traits\EnumBitwise;

class StorageDiv extends Enum
{
    use EnumBitwise;

    public const LOCAL = 1;
}
