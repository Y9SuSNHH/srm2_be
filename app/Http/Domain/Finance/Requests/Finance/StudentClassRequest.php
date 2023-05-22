<?php

namespace App\Http\Domain\Finance\Requests\Finance;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;

class StudentClassRequest extends BaseSearchRequest
{
    protected $casts = [
        'classId'         => Request::CAST_INT,
        'searchStudent'         => Request::CAST_STRING,

    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'classId'      => ['required'],
            'searchStudent'      => [],
        ]);
    }

    public function messages(): array
    {
        return [
            'classId.required' => 'Vui lòng chọn lớp!',
        ];
    }
}