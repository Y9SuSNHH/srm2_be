<?php

namespace App\Http\Domain\Finance\Requests\FinancialCredit;

use App\Helpers\Request;
use Illuminate\Validation\Rule;
use App\Helpers\Rules\Unique;

class CreateRequest extends Request
{
    /**
     * @var array
     */
    protected $casts = [
        'student_profile_id'      => Request::CAST_INT,
        'transaction_id'          => Request::CAST_INT,
        'amount'                  => Request::CAST_INT,
        'purpose'                 => Request::CAST_INT,
        'no'                      => Request::CAST_STRING,
        'note'                    => Request::CAST_STRING,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'student_profile_id' => [
                'required',
                Rule::exists('student_profiles', 'id'),
            ],
            'transaction_id' => [
                'required',
                Rule::exists('transactions', 'id'),
            ],
            'amount' => [
                'required',
                'integer'
            ],
            'purpose' => [
                'required',
                'integer',
            ],
            'no' => [
                'required',
                'string',
            ],
            'note' => [
                'nullable',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_profile_id'  => 'Mã hồ sơ',
            'transaction_id'      => 'Mã giao dịch',
            'amount'              => 'Phí',
            'purpose'             => 'Mục đích',
            'no'                  => 'Đợt',
            'note'                => 'Ghi chú',
        ];
    }
}
