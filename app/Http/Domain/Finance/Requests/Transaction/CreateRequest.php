<?php

namespace App\Http\Domain\Finance\Requests\Transaction;

use App\Eloquent\StudentProfile;
use App\Eloquent\Transaction;
use App\Helpers\Request;
use Illuminate\Validation\Rule;
use App\Helpers\Rules\Unique;
use App\Http\Enum\ApprovalStatus;
use Illuminate\Support\Facades\DB;

class CreateRequest extends Request
{
    /**
     * @var array
     */
    protected $casts = [
        'student_profile_id'         => Request::CAST_INT,
        'code'                       => Request::CAST_STRING,
        'amount'                     => Request::CAST_INT,
        'is_debt'                    => Request::CAST_BOOL,
        'note'                       => Request::CAST_STRING,
        'approval_status'            => Request::CAST_INT,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'params.*.student_profile_id' => [
                'required',
                'integer',
                Rule::exists('student_profiles', 'id'),
            ],
            'params.*.code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($input) {
                    $count = Transaction::query()
                                          ->where('code', $value)
                                          ->where('is_debt', $this->httpRequest()->get('is_debt'))
                                          ->count();
                    if ($count > 0) {
                        $item = collect($input['params'])->where('code', $value)->first();
                        $student = StudentProfile::query()
                        ->where('id', $item['student_profile_id'])
                        ->first();
                        return $fail("Giao dịch của mã hồ sơ {$student->profile_code} đã tồn tại.<br/>");
                    }
                },
            ],
            'params.*.amount' => [
                'required',
                'integer',
            ],
            'params.*.is_debt' => [
                'nullable',
                'boolean',
            ],
            'params.*.note' => [
                'nullable',
                'string',
            ],
            'params.*.approval_status' => [
                'nullable',
                'integer',
                Rule::in(array_values(ApprovalStatus::toArray())),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'params.*.student_profile_id'   => 'Mã hồ sơ',
            'params.*.code'                 => 'Mã giao dịch',
            'params.*.amount'               => 'Phí',
            'params.*.note'                 => 'Ghi chú',
            'params.*.is_debt'              => 'Nợ',
            'params.*.approval_status'      => 'Tình trạng',
        ];
    }
}