<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class PetitionFlowStatus extends Enum
{
    public const REJECT             = 1;
    public const ACCEPT             = 2;
    public const SEND               = 3;
    public const THIRD_PARTY_REJECT = 4;
    public const THIRD_PARTY_ACCEPT = 5;

    /**
     * @return int[]
     */
    public static function learningManager(): array
    {
        return [self::SEND];
    }

    /**
     * @return int[]
     */
    public static function academicAffair(): array
    {
        return [
            self::REJECT,
            self::ACCEPT,
            self::SEND,
        ];
    }

    public static function thirdParty()
    {
        return [
            self::THIRD_PARTY_REJECT,
            self::THIRD_PARTY_ACCEPT,
        ];
    }

//    public static function getByRole()
//    {
//        $roles = auth()->guard()->roleAuthorities();
//        if (in_array(RoleAuthority::LEARNING_MANAGEMENT, $roles)) {
//            return self::learningManager();
//        }
//        if (in_array(RoleAuthority::ACADEMIC_AFFAIRS_OFFICER, $roles)) {
//            return self::academicAffair();
//        }
//        return [];
//    }
}