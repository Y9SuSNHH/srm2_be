<?php

namespace App\Http\Domain\Student\Requests\IgnoreLearningModule;

use App\Eloquent\IgnoreLearningModule;
use App\Eloquent\LearningModule;
use App\Helpers\Request;
use Illuminate\Validation\Rule;

class StoreRequest extends Request
{
    protected $casts = [
        'learning_module' => Request::CAST_ARRAY,
        'reason'          => Request::CAST_STRING,
    ];

    public function prepareInput(array $input): array
    {
        if (array_key_exists('learning_module', $input) && !is_array($input['learning_module'])) {
            $input['learning_module'] = explode(',', $input['learning_module']);
        }

        return $input;
    }

    public function rules(array $input): array
    {
        return [
            'learning_module'   => [
                'array',
                'nullable',
            ],
            'learning_module.*' => [
                'integer',
                Rule::exists(LearningModule::class, 'id'),
                Rule::notIn(IgnoreLearningModule::query()->select('learning_module_id')->whereIn('learning_module_id', $input['learning_module'])->where('student_id', $this->httpRequest()->id)->get()->pluck('learning_module_id'))
            ],
            'reason'            => [
                'nullable',
                'string',
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'learning_module' => 'Mã học phần',
        ];
    }
    
    public function messages(): array
    {
        return [
          'not_in'  => 'Sinh viên này đã được miễn một trong số mã học phần trên',
        ];
    }
}