<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Handover;

use App\Eloquent\Handover;
use App\Eloquent\Staff;
use App\Helpers\Request;
use App\Helpers\Rules\GreaterOrEqualThanColumnValue;
use App\Http\Enum\HandoverStatus;
use App\Http\Enum\ProfileStatus;
use App\Http\Enum\StudentStatus;
use Illuminate\Validation\Rule;
use ReflectionException;

class UpdateRequest extends Request
{
    protected $casts = [
        'staff_id'                   => self::CAST_INT,
        'handover_date'              => self::CAST_CARBON,
        'status'                     => self::CAST_INT,
        'student_status'             => self::CAST_INT,
        'return_student_code_status' => self::CAST_BOOL,
        'no'                         => self::CAST_INT,
        'decision_date'              => self::CAST_CARBON,
//        'is_lock'                => self::CAST_BOOL,
    ];

    /**
     * @param array $input
     * @return array
     * @throws ReflectionException
     */
    public function rules(array $input): array
    {
        $no             = [
            'nullable',
            'integer',
        ];
        $decision_date  = [
            'nullable',
            'date_format:Y-m-d',
        ];
        $student_status = ['nullable'];
        $profile_status = ['nullable'];

        if ((int)$input['status'] === HandoverStatus::SCHOOL_RETURN_SIGN_PROFILE) {
            $no[]             = 'required';
            $decision_date[]  = 'required';
            $student_status[] = 'in:' . StudentStatus::DANG_HOC_DA_CO_QDNH;
            $profile_status[] = 'in:' . ProfileStatus::QDNH_HS_CUNG;
        } else {
            $student_status[] = Rule::in(array_values(StudentStatus::toArray()));
            $profile_status[] = Rule::in(array_values(ProfileStatus::toArray()));
        }

        return [
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
                new GreaterOrEqualThanColumnValue(Handover::getModel()->getTable(), $this->httpRequest()->id, 'status'),
            ],
            'return_student_code_status' => [
                'nullable',
                'boolean',
            ],
//            'is_lock'                => [
//                'nullable',
//                'boolean',
//            ],
            'student_status'             => $student_status,
            'profile_status'             => $profile_status,
            'no'                         => $no,
            'decision_date'              => $decision_date,
        ];
    }

    public function attributes(): array
    {
        return [
            'no' => 'Số quyết định trúng tuyển',
        ];
    }
}