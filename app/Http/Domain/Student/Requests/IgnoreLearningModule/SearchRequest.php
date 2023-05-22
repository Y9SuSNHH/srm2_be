<?php

namespace App\Http\Domain\Student\Requests\IgnoreLearningModule;

use App\Eloquent\Classroom;
use App\Eloquent\LearningModule;
use App\Eloquent\Staff;
use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'profile_code'        => Request::CAST_STRING,
        'student_code'        => Request::CAST_STRING,
        'fullname'            => Request::CAST_STRING,
        'phone_number'        => Request::CAST_INT,
        'staff'               => Request::CAST_ARRAY,
        'classroom'           => Request::CAST_ARRAY,
        'learning_module'     => Request::CAST_ARRAY,
        'first_day_of_school' => Request::CAST_CARBON,
    ];

    public function prepareInput(array $input): array
    {
        if (array_key_exists('classroom', $input) && !is_array($input['classroom'])) {
            $input['classroom'] = explode(',', trim($input['classroom']));
        }
        if (array_key_exists('staff', $input) && !is_array($input['staff'])) {
            $input['staff'] = explode(',', trim($input['staff']));
        }
        if (array_key_exists('learning_module', $input) && !is_array($input['learning_module'])) {
            $input['learning_module'] = explode(',', trim($input['learning_module']));
        }
        return $input;
    }

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'profile_code'      => [
                'nullable',
                'string',
            ],
            'student_code'      => [
                'nullable',
                'string',
            ],
            'fullname'          => [
                'nullable',
                'string',
            ],
            'phone_number'      => [
                'nullable',
                'numeric',
            ],
            'staff'             => [
                'nullable',
                'array',
            ],
            "staff.*"           => [
                'integer',
                Rule::exists(Staff::class, 'id'),
            ],
            'classroom'         => [
                'nullable',
                'array',
            ],
            "classroom.*"       => [
                'integer',
                Rule::exists(Classroom::class, 'id'),
            ],
            'learning_module'   => [
                'nullable',
                'array',
            ],
            "learning_module.*" => [
                'integer',
                Rule::exists(LearningModule::class, 'id'),
            ],
            "first_day_of_school" => [
                'nullable',
                'date',
                'before_or_equal:9999-12-31',
            ],
        ]);
    }
}