<?php

namespace App\Http\Domain\Student\Requests\Petition;

use App\Eloquent\Classroom;
use App\Eloquent\Staff;
use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use App\Http\Enum\PetitionStatus;
use Illuminate\Validation\Rule;

class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'profile_code' => Request::CAST_STRING,
        'student_code' => Request::CAST_STRING,
        'fullname'     => Request::CAST_STRING,
        'phone_number' => Request::CAST_INT,
        'staff'        => Request::CAST_ARRAY,
        'classroom'    => Request::CAST_ARRAY,
        'status'       => Request::CAST_INT,
    ];

    public function prepareInput(array $input): array
    {
        if (array_key_exists('classroom', $input) && !is_array($input['classroom'])) {
            $input['classroom'] = explode(',', trim($input['classroom']));
        }
        if (array_key_exists('staff', $input) && !is_array($input['staff'])) {
            $input['staff'] = explode(',', trim($input['staff']));
        }
        if (array_key_exists('status', $input) && !is_array($input['status'])) {
            $input['status'] = explode(',', trim($input['status']));
        }
        return $input;
    }

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'profile_code' => [
                'nullable',
                'string',
            ],
            'student_code' => [
                'nullable',
                'string',
            ],
            'fullname'     => [
                'nullable',
                'string',
            ],
            'phone_number' => [
                'nullable',
                'numeric',
            ],
            'status'       => [
                'nullable',
                'array',
            ],
            'status.*'       => [
                'integer',
                Rule::in(array_values(PetitionStatus::toArray())),
            ],
            'staff'        => [
                'nullable',
                'array',
            ],
            "staff.*"      => [
                'integer',
                Rule::exists(Staff::class, 'id'),
            ],
            'classroom'    => [
                'nullable',
                'array',
            ],
            "classroom.*"  => [
                'integer',
                Rule::exists(Classroom::class, 'id'),
            ],
        ]);
    }

    public function attributes(): array
    {
        return [
            'staff'   => 'QLHT',
            'staff.*' => 'QLHT',
            'status' => 'Trạng thái đơn từ',
            'status.*' => 'Trạng thái đơn từ',
        ];
    }
}