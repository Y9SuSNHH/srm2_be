<?php

namespace App\Http\Enum;

use App\Helpers\Enum;
use App\Helpers\Traits\EnumBitwise;

/**
 * Class PermissionAction
 * @package App\Http\Enum
 *
 * @method static PermissionAction READ()
 * @method static PermissionAction CREATE()
 * @method static PermissionAction EDIT()
 * @method static PermissionAction DELETE()
 */
class PermissionAction extends Enum
{
    use EnumBitwise;

    const READ = 0;

    const CREATE = 1;

    const EDIT = 2;

    const DELETE = 4;
}
