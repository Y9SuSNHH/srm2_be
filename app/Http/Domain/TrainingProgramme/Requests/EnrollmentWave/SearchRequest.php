<?php

namespace App\Http\Domain\TrainingProgramme\Requests\EnrollmentWave;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\EnrollmentWave
 *
 * @property int|null $area_id
 * @property Carbon|null $first_day_of_school
 * @property int|null $school_id
 * @property int|null $enrollment_wave_year
 * @property int|null $group_number
 */
class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'group_number' => Request::CAST_INT,
        'area_id' => Request::CAST_INT,
        'first_day_of_school' => Request::CAST_CARBON,
        'school_id' => Request::CAST_INT,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'group_number' => [
                'nullable',
                Rule::exists('enrollment_waves', 'group_number'),
            ],
            'area_id' => [
                'nullable',
                Rule::exists('areas', 'id'),
            ],
            'first_day_of_school' => [
                'nullable',
                Rule::exists('enrollment_waves', 'first_day_of_school'),
            ],
            'school_id' => [
                'nullable',
                Rule::exists('schools', 'id'),
            ],
            'enrollment_wave_year' => 'nullable|integer'
        ]);
    }
}
