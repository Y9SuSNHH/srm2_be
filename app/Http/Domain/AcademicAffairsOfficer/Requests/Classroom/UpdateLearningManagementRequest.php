<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom;

use App\Helpers\Request;
use App\Http\Enum\StaffTeam;
use Illuminate\Validation\Rule;

/**
 * Class UpdateLearningManagementRequest
 * @package App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom
 *
 * @property int $staff_id
 * @property array $classrooms
 */
class UpdateLearningManagementRequest extends Request
{
    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'staff_id' => [
                'required',
                Rule::exists('staffs', 'id')->where('team', StaffTeam::LEARNING_MANAGEMENT),
            ],
            'classrooms' => 'required|array',
            'classrooms.*' => [
                'required',
                'integer',
                Rule::exists('classrooms', 'id'),
            ],
        ];
    }
}