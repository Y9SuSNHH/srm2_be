<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Handover;

use App\Helpers\Request;
use App\Http\Enum\ProfileStatus;
use App\Http\Enum\StudentStatus;
use Illuminate\Validation\Rule;
use ReflectionException;

class DeleteRequest extends Request
{
    protected $casts = [
        'student_status' => self::CAST_INT,
        'profile_status' => self::CAST_INT,
    ];

    /**
     * @param array $input
     * @return array[]
     * @throws ReflectionException
     */
    public function rules(array $input): array
    {
        return [
            'student_status' => [
                'required',
                Rule::in(StudentStatus::toArray()),
            ],
            'profile_status' => [
                'required',
                Rule::in(ProfileStatus::toArray()),
            ],
        ];
    }
}