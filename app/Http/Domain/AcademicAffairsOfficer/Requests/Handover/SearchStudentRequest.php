<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Handover;

use App\Eloquent\Classroom;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Validation\Rule;

class SearchStudentRequest extends BaseSearchRequest
{
    protected $casts = [
        'profile_receive_area' => self::CAST_STRING,
        'receive_date'         => self::CAST_CARBON,
        'use_handover_id'      => self::CAST_BOOL,
        'classroom_id'         => self::CAST_INT,
        'profile_code'         => self::CAST_STRING,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input),[
            'profile_receive_area' => [
                'nullable',
                'string',
            ],
            'receive_date'         => [
                'nullable',
                'date',
            ],
            'use_handover_id'      => [
                'nullable',
                'boolean',
            ],
            'classroom_id'         => [
                'integer',
                Rule::exists(Classroom::class, 'id'),
            ],
            'profile_code'         => [
                'nullable',
                'string',
            ],
        ]);
    }
}