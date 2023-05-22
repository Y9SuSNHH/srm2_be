<?php

namespace App\Http\Domain\Finance\Requests\Finance;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Carbon\Carbon;

class TuitionRequest extends BaseSearchRequest
{
    protected $casts = [
        'startDate'         => Request::CAST_CARBON,
        'receivable_date'   => Request::CAST_CARBON,
        'semester'          => Request::CAST_INT,
        'staff'             => Request::CAST_INT,
        'classId'           => Request::CAST_INT,
        'fullname'          => Request::CAST_STRING,
        'student_code'      => Request::CAST_STRING,
        'studentStatus'     => Request::CAST_INT,
        'transaction_ids'   => Request::CAST_STRING,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'startDate'         => ['date_format:Y-m-d'],
            'receivable_date'   => ['date_format:Y-m-d'],
            'semester'          => [],
            'staff'             => [],
            'classId'           => [],
            'fullname'          => [],
            'student_code'      => [],
            'studentStatus'     => [],
            'transaction_ids'   => [],
        ]);
    }
}
