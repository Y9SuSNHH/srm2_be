<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class PetitionStatus extends Enum
{

    public const LEARNING_MANAGEMENT_SEND = 0;
    public const ACADEMIC_AFFAIR_REJECT   = 1;
    public const ACADEMIC_AFFAIR_ACCEPT   = 2;
    public const ACADEMIC_AFFAIR_SEND     = 3;
    public const SCHOOL_REJECT            = 4;
    public const SCHOOL_ACCEPT            = 5;

    /**
     * @return int[]
     */
    public static function academicAffair(): array
    {
        return [
            self::ACADEMIC_AFFAIR_REJECT,
            self::ACADEMIC_AFFAIR_ACCEPT,
            self::ACADEMIC_AFFAIR_SEND,
        ];
    }

    /**
     * @return int[]
     */
    public static function thirdParty(): array
    {
        return [
            self::SCHOOL_ACCEPT,
            self::SCHOOL_REJECT,
        ];
    }

    /**
     * @return int[]
     */
    public static function reject(): array
    {
        return [
            self::ACADEMIC_AFFAIR_REJECT,
            self::SCHOOL_REJECT,
        ];
    }
}
