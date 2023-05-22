<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Handover;

use App\Eloquent\Area;
use App\Eloquent\Handover;
use App\Eloquent\Staff;
use App\Helpers\Request;
use App\Http\Enum\HandoverStatus;
use App\Http\Enum\StudentStatus;
use Illuminate\Validation\Rule;
use ReflectionException;

class StoreRequest extends Request
{
    protected $casts = [
        'area_id'                    => self::CAST_INT,
        'first_day_of_school'        => self::CAST_CARBON,
        'code'                       => self::CAST_STRING,
        'staff_id'                   => self::CAST_INT,
        'handover_date'              => self::CAST_CARBON,
        'status'                     => self::CAST_INT,
        'student_status'             => self::CAST_INT,
        'return_student_code_status' => self::CAST_BOOL,
        'no'                         => self::CAST_INT,
        'decision_date'              => self::CAST_CARBON,
    ];

    /**
     * @param array $input
     * @return array
     * @throws ReflectionException
     */
    public function rules(array $input): array
    {
        return [
            'area_id'                       => [
                'required',
                'integer',
                'min:0',
                Rule::exists(Area::class, 'id'),
            ],
            'first_day_of_school'        => [
                'required',
                'nullable',
                'date_format:Y-m-d',
            ],
            'code'                       => [
                'required',
                'string',
                Rule::unique(Handover::class, 'code'),
            ],
            'staff_id'                   => [
                'required',
                Rule::exists(Staff::class, 'id'),
            ],
            'handover_date'              => [
                'required',
                'date_format:Y-m-d',
            ],
            'status'                     => [
                'required',
                Rule::in(array_values(HandoverStatus::toArray())),
            ],
            'student_status'             => [
                'nullable',
                Rule::in(array_values(StudentStatus::toArray())),
            ],
            'return_student_code_status' => [
                'nullable',
                'boolean',
            ],
            'no'                         => [
                'nullable',
                'integer',
            ],
            'decision_date'              => [
                'nullable',
                'date_format:Y-m-d',
            ],
        ];
    }
}