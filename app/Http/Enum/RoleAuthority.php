<?php

namespace App\Http\Enum;

use App\Helpers\Enum;
use App\Helpers\Traits\EnumBitwise;

/**
 * Class RoleAuthority
 * @package App\Http\Enum
 *
 * @method static RoleAuthority SUPPER_ADMIN()
 * @method static RoleAuthority ADMISSION_ADVISER()
 * @method static RoleAuthority ACADEMIC_AFFAIRS_OFFICER()
 * @method static RoleAuthority LEARNING_MANAGEMENT()
 * @method static RoleAuthority ACCOUNTANT()
 * @method static RoleAuthority PM()
 */
class RoleAuthority extends Enum
{
    use EnumBitwise;

    public const SUPPER_ADMIN = 128;
    /**
     * Tu van tuyen sinh
     */
    public const ADMISSION_ADVISER = 1;

    /**
     * Giao vu
     */
    public const ACADEMIC_AFFAIRS_OFFICER = 2;

    /**
     * Quan ly hoc tap
     */
    public const LEARNING_MANAGEMENT = 4;

    /*
     * Ke toan
     */
    public const ACCOUNTANT = 8;

    /**
     * Van hanh trung tam
     */
    public const PROGRAM_COORDINATOR = 32;

    /**
     * PM
     */
    public const PM = 16;

    /**
     * @return int[]
     */
    public static function withoutChooseSchool(): array
    {
        return [self::ADMISSION_ADVISER];
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    public function check(): bool
    {
        $role_authority = auth()->guard()->roleAuthority() ?? 0;
        return $this->validate($role_authority);
    }
}
