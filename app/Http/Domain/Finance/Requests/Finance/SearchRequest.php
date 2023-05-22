<?php

namespace App\Http\Domain\Finance\Requests\Finance;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Carbon\Carbon;

class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'g_date'            => Request::CAST_CARBON,
        'receivable_date'   => Request::CAST_CARBON,
        'semester'          => Request::CAST_INT,
        'major'             => Request::CAST_INT,
        'staff'             => Request::CAST_INT,
        'classId'           => Request::CAST_INT,
        'fullname'          => Request::CAST_STRING,
        'student_code'      => Request::CAST_STRING,
        'class_semester'    => Request::CAST_STRING,
        'transaction_ids'   => Request::CAST_STRING,
        'purpose'           => Request::CAST_INT,
        'studentStatus'     => Request::CAST_INT,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'g_date'            => ['date_format:Y-m-d'],
            'receivable_date'   => ['date_format:Y-m-d'],
            'semester'          => [],
            'major'             => [],
            'staff'             => [],
            'classId'           => [],
            'fullname'          => [],
            'student_code'      => [],
            'class_semester'    => [],
            'transaction_ids'   => [],
            'purpose'           => [],
            'studentStatus'     => [],
        ]);
    }
}
