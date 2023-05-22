<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Handover;

use App\Eloquent\StudentProfile;
use App\Helpers\Request;
use Illuminate\Validation\Rule;

class UpdateStudentProfilesRequest extends Request
{
    protected $casts = [
        'student_profile_ids' => self::CAST_ARRAY,
        'check_all'           => self::CAST_BOOL,
    ];

    public function rules(array $input): array
    {
        return [
            'check_all'             => [
                'nullable',
                'boolean',
            ],
            'student_profile_ids'   => [
                'required_if:check_all,1',
                'array',
            ],
            'student_profile_ids.*' => [
                'required_if:check_all,1',
                'integer',
                Rule::exists(StudentProfile::class, 'id'),
            ],
        ];
    }
}