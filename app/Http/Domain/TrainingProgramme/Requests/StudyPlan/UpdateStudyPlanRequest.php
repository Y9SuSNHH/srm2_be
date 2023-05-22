<?php

namespace App\Http\Domain\TrainingProgramme\Requests\StudyPlan;

use App\Eloquent\Classroom;
use App\Helpers\Request;
use Illuminate\Validation\Rule;
use App\Eloquent\StudyPlan;
use App\Helpers\Rules\Unique;
use Carbon\Carbon;

class UpdateStudyPlanRequest extends Request
{
    /**
     * @param array $input
     * @return array[]
     */
    public function rules(array $input): array
    {
        $now = Carbon::now();
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
                Rule::exists('learning_modules', 'id'),
                function ($attribute, $value, $fail) {
                    $query = StudyPlan::query()
                        ->where('classroom_id', $this->httpRequest()->get('classroom_id'))
                        ->where('semester', $this->httpRequest()->get('semester'))
                        ->where('slot', $this->httpRequest()->get('slot'))
                        ->where('learning_module_id', $this->httpRequest()->get('learning_module_id'))
                        ->where('id', '<>', $this->httpRequest()->id)
                        ->count();
                    if ($query > 0) {
                        return $fail('Sửa KHHT thất bại, vì học phần đã tồn tại');
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
                        ->where('id', '<>', $this->httpRequest()->id)
                        ->count();
                    if ($query > 0) {
                        return $fail('Sửa KHHT thất bại, vì mã môn học đã tồn tại');
                    }
                },
            ],
            'study_began_date' => [
                'required',
                function ($attribute, $value, $fail) use ($now){
                    $query = StudyPlan::query()
                        ->where('id', $this->httpRequest()->id)
                        ->where('study_began_date', '<', $now)
                        ->first();
                    if ($query) {
                        return $fail('Sửa KHHT thất bại, vì KHHT đã được sử dụng');
                    }
                },
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