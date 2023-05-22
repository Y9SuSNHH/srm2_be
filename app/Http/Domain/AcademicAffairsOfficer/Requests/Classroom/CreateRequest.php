<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom;

use App\Helpers\Request;
use App\Http\Enum\StaffTeam;
use Illuminate\Validation\Rule;

/**
 * Class CreateRequest
 * @package App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom
 *
 * @property int $major_id
 * @property int $enrollment_object_id
 * @property int $area_id
 * @property int $enrollment_wave_id
 * @property int $object_classification_id
 * @property int $staff_id
 * @property string $code
 */
class CreateRequest extends Request
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
                Rule::unique('classrooms', 'code')
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'major_id' => 'Ngành đào tạo',
            'enrollment_object_id' => 'Đối tượng',
            'area_id' => 'Trạm',
            'enrollment_wave_id' => 'Đợt khai giảng',
            'staff_id' => 'QLHT',
            'code' => 'Mã lớp',
        ];
    }
}
