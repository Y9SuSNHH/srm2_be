<?php

namespace App\Http\Domain\Receivable\Requests\Receivable;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Carbon\Carbon;

/**
 * Class SearchRequest
 * @package App\Http\Request\Receivable
 *
 * @property \Carbon\Carbon $began_date
 * @property $dot_hoc
 * @property $ma_nganh
 * @property $quan_ly_hoc_tap
 * @property $lop_quan_ly
 */
class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'began_date'        => Request::CAST_CARBON,
        'dot_hoc'           => Request::CAST_INT,
        'ma_nganh'          => Request::CAST_INT,
        'quan_ly_hoc_tap'   => Request::CAST_INT,
        'lop_quan_ly'       => Request::CAST_INT,

    ];

    public function prepareInput(array $input): array
    {
        return $input;
    }
   
     public function rules(array $input): array
     {
        return array_merge(parent::rules($input), [
            'began_date'       => ['required','date_format:Y-m-d'],
            'dot_hoc'          => ['nullable'],
            'ma_nganh'         => ['nullable'],
            'quan_ly_hoc_tap'  => ['nullable'],
            'lop_quan_ly'      => ['nullable']

        ]);
     }

     
    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'began_date'         => 'Ngày bắt đầu thu',
            'dot_hoc'            => 'Đợt học',
            'ma_nganh'           => 'Mã ngành',
            'quan_ly_hoc_tap'    => 'Quản lý học tập',
            'lop_quan_ly'        => 'Lớp quản lý',
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'numeric'     => ':attribute phải là kiểu số',
            'date_format' => ':attribute phải là kiểu ngày định dạng Y-m-d',
        ];
    }
}
