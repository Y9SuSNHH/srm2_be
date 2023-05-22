<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class ProfileStatus extends Enum
{
    public const F30_GV = 1;
    public const F30_DU_HS_SCAN = 2;
    public const F30_DU_HS_SCAN_BAN_GIAO_TVU = 3;
    public const DU_HS_CUNG_LUU_GV = 4;
    public const DU_HS_CUNG_DA_BAN_GIAO_TVU = 5;
    public const QDNH_HS_SCAN = 6;
    public const QDNH_HS_CUNG = 7;
    public const DU_HS_CUNG_DA_BAN_GIAO_TVU_LOI_CHO_BOSUNG = 8;
    public const NGHI_HOC = 9;
    public const HS_CUNG_NOP_BEN_CTEC = 10;

    /**
     * @param $value
     * @return bool
     * @throws \ReflectionException
     */
    public static function isF30($value): bool
    {
        return in_array($value, [ProfileStatus::F30_GV,ProfileStatus::F30_DU_HS_SCAN,ProfileStatus::F30_DU_HS_SCAN_BAN_GIAO_TVU], true);
    }

    public static function statusToValue(string $status) {
        $statusArray = [
            "F30_GV" => 1,
            "F30_DU_HS_SCAN" => 2,
            "F30_DU_HS_SCAN_BAN_GIAO_TVU" => 3,
            "DU_HS_CUNG_LUU_GV" => 4,
            "DU_HS_CUNG_DA_BAN_GIAO_TVU" => 5, 
            "QDNH_HS_SCAN" => 6,
            "QDNH_HS_CUNG" => 7,
            "DU_HS_CUNG_DA_BAN_GIAO_TVU_LOI_CHO_BOSUNG" =>8,
            "NGHI_HOC" => 9,
            "HS_CUNG_NOP_BEN_CTEC" => 10
        ];
        return $statusArray[$status];
    }

    public static function existStatus($value): bool
    {
        return in_array($value, ['F30_GV','F30_DU_HS_SCAN','F30_DU_HS_SCAN_BAN_GIAO_TVU','DU_HS_CUNG_LUU_GV','DU_HS_CUNG_DA_BAN_GIAO_TVU','QDNH_HS_SCAN','QDNH_HS_CUNG','DU_HS_CUNG_DA_BAN_GIAO_TVU_LOI_CHO_BOSUNG','NGHI_HOC','HS_CUNG_NOP_BEN_CTEC'], true);
    }


}
