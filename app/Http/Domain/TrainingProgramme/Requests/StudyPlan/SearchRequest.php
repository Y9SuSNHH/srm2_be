<?php

namespace App\Http\Domain\TrainingProgramme\Requests\StudyPlan;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\StudyPlan
 *
 * @property int|null $area_id
 * @property int|null $first_day_of_school
 * @property int|null $major_id
 * @property int|null $class_id
 * @property int|null $slot
 */
class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'area_id'               => Request::CAST_INT,
        'first_day_of_school'   => Request::CAST_CARBON,
        'major_id'              => Request::CAST_INT,
        'class_id'              => Request::CAST_INT,
        'semester'              => Request::CAST_INT,
        'slot'                  => Request::CAST_INT,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'area_id' => [
                'nullable',
            ],
            'first_day_of_school' => [
                'nullable',
            ],
            'major_id' => [
                'nullable',
            ],
            'class_id' => [
                'nullable',
            ],
            'semester' => [
                'nullable',
            ],
            'slot' => [
                'nullable',
            ],
        ]);
    }
}
