<?php

namespace App\Http\Domain\Student\Requests\Student;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\Student\Requests\Student
 *
 * @property int|null $area_id
 * @property Carbon|null $first_day_of_school
 * @property int|null $school_id
 * @property int|null $enrollment_wave_year
 */
class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'fullname'            => Request::CAST_STRING,
        'student_code'        => Request::CAST_STRING,
        'profile_code'        => Request::CAST_STRING,
        'account'             => Request::CAST_STRING,
        'phone_number'        => Request::CAST_STRING,
        'area'                => Request::CAST_STRING,
        'first_day_of_school' => Request::CAST_CARBON,
        'staff'               => Request::CAST_STRING,
        'class'               => Request::CAST_STRING,
        'student_status'      => Request::CAST_STRING,
        'major'               => Request::CAST_STRING,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'fullname'            => [
                'nullable',
            ],
            'student_code'        => [
                'nullable',
            ],
            'profile_code'        => [
                'nullable',
            ],
            'account'             => [
                'nullable',
            ],
            'phone_number'        => [
                'nullable',
                'numeric',
            ],
            'area'                => [
                'nullable',
            ],
            'first_day_of_school' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'staff'               => [
                'nullable',
            ],
            'class'               => [
                'nullable',
            ],
            'student_status'      => [
                'nullable',
            ],
            'major'      => [
                'nullable',
            ],
        ]);
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'fullname'            => 'Họ và tên',
            'student_code'        => 'Mã sinh viên',
            'profile_code'        => 'Mã hồ sơ',
            'account'             => 'Tài khoản học tập',
            'phone_number'        => 'Số điện thoại',
            'area'                => 'Khu vực',
            'first_day_of_school' => 'Ngày khai giảng',
            'staff'               => 'QLHT',
            'class'               => 'Lớp',
            'student_status'      => 'Trạng thái sinh viên',
            'major'               => 'Ngành'
        ];
    }
}
