<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class StudentStatus extends Enum
{
    public const XOA_QUYET_DINH_SINH_VIEN = 0;

    public const NGHI_HOC = 3;

    public const BAO_LUU = 4;

    public const TAM_NGUNG_HOC_DO_CHUA_HS_HP = 5;

    public const DANG_HOC_CHUA_HS = 6;

    public const DANG_HOC_CHO_QDNH = 7;

    public const DANG_HOC_DA_CO_QDNH = 8;

    public const DA_TOT_NGHIEP = 9;

    /**
     * @return string[]
     */
    public static function studentInClass(): array
    {
        return [
            StudentStatus::DANG_HOC_CHUA_HS,
            StudentStatus::DANG_HOC_CHO_QDNH,
            StudentStatus::DANG_HOC_DA_CO_QDNH
        ];
    }
    
    /**
     * statusForReports
     *
     * @param  mixed $key
     * @return string
     */
    public static function statusForReports($key): string
    {
        $status = [
            StudentStatus::XOA_QUYET_DINH_SINH_VIEN     => '00_XOA_QUYET_DINH_SINH_VIEN',
            StudentStatus::NGHI_HOC                     => '03_NGHI_HOC',
            StudentStatus::BAO_LUU                      => '04_BAO_LUU',
            StudentStatus::TAM_NGUNG_HOC_DO_CHUA_HS_HP  => '05_TAM_NGUNG_HOC_DO_CHUA_HS_HP',
            StudentStatus::DANG_HOC_CHUA_HS             => '06_DANG_HOC_CHUA_HS',
            StudentStatus::DANG_HOC_CHO_QDNH            => '07_DANG_HOC_CHO_QĐNH',
            StudentStatus::DANG_HOC_DA_CO_QDNH          => '08_DANG_HOC_DA_CO_QĐNH',
            StudentStatus::DA_TOT_NGHIEP                => '09_DA_TOT_NGHIEP',
        ];
        
        return $status[$key];
    }

    public static function checkExistStatus(string $status) {
        $statusArray = [
            '00_XOA_QUYET_DINH_SINH_VIEN'      => StudentStatus::XOA_QUYET_DINH_SINH_VIEN,
            '03_NGHI_HOC'                      => StudentStatus::NGHI_HOC  ,
            '04_BAO_LUU'                       => StudentStatus::BAO_LUU ,
            '05_TAM_NGUNG_HOC_DO_CHUA_HS_HP'   => StudentStatus::TAM_NGUNG_HOC_DO_CHUA_HS_HP ,
            '06_DANG_HOC_CHUA_HS'              =>  StudentStatus::DANG_HOC_CHUA_HS ,
            '07_DANG_HOC_CHO_QDNH'             => StudentStatus::DANG_HOC_CHO_QDNH,
            '08_DANG_HOC_DA_CO_QDNH'           => StudentStatus::DANG_HOC_DA_CO_QDNH ,
            '09_DA_TOT_NGHIEP'                 => StudentStatus::DA_TOT_NGHIEP 
        ];
        return $statusArray[$status] || null;
    }

    /**
     * @return string[]
     */
    public static function donTuGenerateAble(): array
    {
        return [
            StudentStatus::NGHI_HOC,
            StudentStatus::BAO_LUU,
            StudentStatus::TAM_NGUNG_HOC_DO_CHUA_HS_HP,
            StudentStatus::DANG_HOC_CHUA_HS,
            StudentStatus::DANG_HOC_CHO_QDNH,
            StudentStatus::DANG_HOC_DA_CO_QDNH,
            StudentStatus::DA_TOT_NGHIEP,
        ];
    }
}
