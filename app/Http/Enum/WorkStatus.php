<?php

namespace App\Http\Enum;

use App\Helpers\Enum;
use App\Helpers\Traits\EnumBitwise;

/**
 * Class WorkStatus
 * @package App\Http\Enum
 *
 * @method WorkStatus WAIT()
 * @method WorkStatus FAIL()
 * @method WorkStatus COMPLETE()
 */
class WorkStatus extends Enum
{
    use EnumBitwise;

    public const WAIT = 1;
    public const FAIL = 16;
    public const COMPLETE = 128;

    /**
     * @param $base
     * @param $key
     * @return string
     */
    public static function defineLang($base, $key): string
    {
        $lang = ['work_status' => [
            'WAIT' => 'Đợi',
            'FAIL' => 'Thất bại',
            'COMPLETE' => 'Hoàn thành',
        ]];

        return $lang[$base][$key];
    }
}