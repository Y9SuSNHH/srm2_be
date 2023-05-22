<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom;

use App\Helpers\Request;
use App\Http\Enum\StaffTeam;
use Illuminate\Validation\Rule;

class UpdateRequest extends Request
{
    /**
     * @param array $input
     * @return array[]
     */
    public function rules(array $input): array
    {
        return [
            'major_id' => [
                'required',
                Rule::exists('majors', 'id'),
            ],
            'enrollment_object_id' => [
                'required',
                Rule::exists('enrollment_objects', 'id'),
            ],
            'area_id' => [
                'required',
                Rule::exists('areas', 'id'),
            ],
            'enrollment_wave_id' => [
                'required',
                Rule::exists('enrollment_waves', 'id'),
            ],
            'staff_id' => [
                'nullable',
                Rule::exists('staffs', 'id')->where('team', StaffTeam::LEARNING_MANAGEMENT),
            ],
            'code' => [
                'required',
                Rule::unique('classrooms', 'code')->ignore($this->httpRequest()->id)
            ],
        ];
    }
}
