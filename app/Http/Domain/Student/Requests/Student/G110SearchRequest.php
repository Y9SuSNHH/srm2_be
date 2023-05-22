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
class G110SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'area'                => Request::CAST_STRING,
        'first_day_of_school' => Request::CAST_CARBON,
        'staff'               => Request::CAST_STRING,
        'major'               => Request::CAST_STRING,
        'student_status'      => Request::CAST_STRING,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
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
            'student_status'      => [
                'nullable',
            ],
            'major' => [
                'nullable',
            ]
        ]);
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'area'                => 'Khu vực',
            'first_day_of_school' => 'Ngày khai giảng',
            'staff'               => 'QLHT',
            'major'               => 'Ngành',
            'student_status'      => 'Trạng thái sinh viên',
        ];
    }
}
