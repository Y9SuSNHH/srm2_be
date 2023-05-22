<?php

namespace App\Http\Domain\TrainingProgramme\Requests\LearningModule;

use App\Eloquent\LearningModule;
use App\Helpers\Request;
use App\Http\Enum\GradeSettingDiv;
use Illuminate\Validation\Rule;
use App\Helpers\Rules\Unique;

class CreateLearningModuleRequest extends Request
{
    /**
     * @var array
     */
    protected $casts = [
        'school_id'                       => Request::CAST_INT,
        'subject_id'                      => Request::CAST_INT,
        'code'                            => Request::CAST_STRING,
        'amount_credit'                   => Request::CAST_INT,
        'alias'                           => Request::CAST_STRING,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'school_id' => [
                Rule::exists('schools', 'id'),
            ],
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id'),
            ],
            'code' => [
                'required',
                (new Unique(LearningModule::class, 'code'))
                    ->transformMessage(function ($attribute, $value) {
                        return "Mã học phần đã tồn tại";
                    }),
            ],
            'amount_credit' => [
                'required',
                'integer'
//                (new Unique(LearningModule::class, 'amount_credit'))
//                    ->where('subject_id', $this->httpRequest()->get('subject_id'))
//                    ->transformMessage(function ($attribute, $value) {
//                        return "Học phần này đã có trong cơ sở dữ liệu";
//                    }),
            ],
            'alias' => [
                'nullable',
            ],
            'grade_setting_div' => [
                'required',
                'integer',
                Rule::in(GradeSettingDiv::toArray()),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'school_id' => 'Mã trường',
            'subject_id' => 'Mã môn học',
            'code' => 'Mã học phần',
            'amount_credit' => 'Số tín chỉ',
            'alias' => 'Alias',
        ];
    }

}