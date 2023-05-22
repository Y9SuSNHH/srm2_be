<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class CreateBatchRequest
 * @package App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom
 *
 * @property int $area_id
 * @property int $enrollment_wave_id
 * @property array $items
 */
class CreateBatchRequest extends Request
{
    protected $casts = [
        'area_id' => Request::CAST_INT,
        'enrollment_wave_id' => Request::CAST_INT,
        'items' => Request::CAST_ARRAY,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'area_id' => [
                'required',
                Rule::exists('areas', 'id'),
            ],
            'enrollment_wave_id' => [
                'required',
                Rule::exists('enrollment_waves', 'id'),
            ],
            'items' => [
                'required',
                'array',
            ],
            'items.*.major_id' => [
                'required',
                Rule::exists('majors', 'id'),
            ],
            'items.*.enrollment_object_id' => [
                'required',
                Rule::exists('enrollment_objects', 'id'),
            ],
            'items.*.suffixes' => [
                'required',
                'string',
                'min:1'
            ],
        ];
    }
}
