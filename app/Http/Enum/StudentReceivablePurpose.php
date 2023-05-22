<?php

namespace App\Http\Enum;

class StudentReceivablePurpose extends ReceivablePurpose
{
    /**
     * @param $name
     * @return int
     */
    public static function getValueByKeyVi($name): int
    {
        $vi_enum = [
            'LE_PHI_XET_TUYEN'  => 0,
            'HOC_PHI'           => 1,
            'PHI_HOC_LAI'       => 2,
            'PHI_THI_LAI'       => 3,
            'PHI_MIEN_MON'      => 4,
            'LE_PHI_TOT_NGHIEP' => 5,
            'RUT_HOC_PHI '      => 6,
            'PHI_THE_SV'        => 7,
        ];
        return $vi_enum[strtoupper($name)];
    }

    /**
     * @param $key
     * @return string
     */
    public static function getValueByKey($key): string
    {
        $enum = [
            0 => 'LE_PHI_XET_TUYEN',
            1 => 'HOC_PHI',
            2 => 'PHI_HOC_LAI',
            3 => 'PHI_THI_LAI',
            4 => 'PHI_MIEN_MON',
            5 => 'LE_PHI_TOT_NGHIEP',
            6 => 'RUT_HOC_PHI',
            7 => 'PHI_THE_SV',
        ];
        return $enum[$key];
    }
}