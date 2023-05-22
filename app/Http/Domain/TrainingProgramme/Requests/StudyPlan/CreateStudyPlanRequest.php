<?php

namespace App\Http\Domain\TrainingProgramme\Requests\StudyPlan;

use App\Eloquent\StudyPlan;
use App\Helpers\Request;
use Illuminate\Validation\Rule;
use App\Helpers\Rules\Unique;

class CreateStudyPlanRequest extends Request
{
    /**
     * @var array
     */
    protected $casts = [
        'classroom_id'                       => Request::CAST_INT,
        'semester'                           => Request::CAST_INT,
        'slot'                               => Request::CAST_INT,
        'learning_module_id'                 => Request::CAST_INT,
        'subject_id'                         => Request::CAST_INT,
        'study_began_date'                   => Request::CAST_CARBON,
        'study_ended_date'                   => Request::CAST_CARBON,
        'day_of_the_test'                    => Request::CAST_CARBON,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'classroom_id' => [
                'required',
                Rule::exists('classrooms', 'id'),
            ],
            'semester' => [
                'required',
            ],
            'slot' => [
                'required',
            ],
            'learning_module_id' => [
                'required',
                Rule::exists('training_program_items', 'learning_module_id'),
                function ($attribute, $value, $fail) {
                    $query = StudyPlan::query()
                        ->where('classroom_id', $this->httpRequest()->get('classroom_id'))
                        ->where('semester', $this->httpRequest()->get('semester'))
                        ->where('slot', $this->httpRequest()->get('slot'))
                        ->where('learning_module_id', $this->httpRequest()->get('learning_module_id'))
                        ->count();
                    if ($query > 0) {
                        return $fail('Thêm kế hoạch học tập thất bại, vì học phần đã tồn tại');
                    }
                },
            ],
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id'),
                function ($attribute, $value, $fail) {
                    $query = StudyPlan::query()
                        ->where('subject_id', $this->httpRequest()->get('subject_id'))
                        ->where('classroom_id', $this->httpRequest()->get('classroom_id'))
                        ->count();
                    if ($query > 0) {
                        return $fail('Thêm kế hoạch học tập thất bại, vì mã môn học đã tồn tại');
                    }
                },
            ],
            'study_began_date' => [
                'required',
            ],
            'study_ended_date' => [
                'required',
            ],
            'day_of_the_test' => [
                'required',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'classroom_id' => 'Mã lớp',
            'semester' => 'Đợt',
            'slot' => 'Slot',
            'learning_module_id' => 'Mã học phần',
            'subject_id' => 'Mã môn học',
            'study_began_date' => 'Ngày bắt đầu',
            'study_ended_date' => 'Ngày kết thúc',
            'day_of_the_test' => 'Ngày thi',
        ];
    }
}
