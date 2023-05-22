<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class ReferenceType extends Enum
{
    public const STAFF = 1;

    public const PETITION = 2;

    public const WORKFLOW = 3;
    public const HANDOVER = 4;
}
