<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class ObjectClassification extends Enum
{
    public const TOGETHER = 'C';
    public const SAME = 'G';
    public const OTHER = 'K';

    public static function defineLang($base, $key): string
    {
        $lang = ['object_classification' => [
            'TOGETHER' => 'Cùng ngành',
            'SAME' => 'Gần ngành',
            'OTHER' => 'Khác ngành',
        ]];

        return $lang[$base][$key];
    }

    public static function getValueByKey($key): string
    {
        $enum = [
            ObjectClassification::TOGETHER  => 'cùng ngành',
            ObjectClassification::SAME      => 'gần ngành',
            ObjectClassification::OTHER     => 'khác ngành',
        ];
        return $enum[$key];
    }

    public static function findValueF111($key): string
    {
        $enum = [
            'cùng'    => ObjectClassification::TOGETHER,
            'gần'     => ObjectClassification::SAME,
            'khác'    => ObjectClassification::OTHER,
        ];
        return $enum[$key] ?? '';
    }
}