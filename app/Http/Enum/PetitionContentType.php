<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class PetitionContentType extends Enum
{
    public const RESERVE                         = 1;
    public const LEAVE_SCHOOL                    = 2;
    public const PAUSE_STUDYING                  = 3;
    public const CONTINUE_TO_STUDY_CHANGE_MAJORS = 4;
    public const CHANGE_AREA                     = 5;


    /**
     * @return int[]
     */
    public static function student(): array
    {
        return [
            self::RESERVE,
            self::LEAVE_SCHOOL,
            self::PAUSE_STUDYING,
        ];
    }

    /**
     * @return int[]
     */
    public static function area(): array
    {
        return [
            self::CHANGE_AREA,
        ];
    }

    /**
     * @return int[]
     */
    public static function classroom(): array
    {
        return [
            self::CONTINUE_TO_STUDY_CHANGE_MAJORS,
        ];
    }

    /**
     * @param int $content_type
     * @return null[]
     */
    public static function getBaseJsonContent(int $content_type): array
    {
        $array = [
            PetitionContent::STUDENT_STATUS => null
        ];

        $classroom = [PetitionContent::CLASSROOM => null];

        if (in_array($content_type, self::classroom()) || in_array($content_type, self::area())) {
            $array = array_merge($array, $classroom);
        }
        return $array;
    }

    /**
     * @param $contentType
     * @return int
     */
    public static function getNewStudentStatus($contentType): int
    {
        return match ($contentType) {
            self::RESERVE => StudentStatus::BAO_LUU,
            self::LEAVE_SCHOOL => StudentStatus::NGHI_HOC,
            self::PAUSE_STUDYING => StudentStatus::TAM_NGUNG_HOC_DO_CHUA_HS_HP,
            default => StudentStatus::DANG_HOC_DA_CO_QDNH,
        };
    }

}
