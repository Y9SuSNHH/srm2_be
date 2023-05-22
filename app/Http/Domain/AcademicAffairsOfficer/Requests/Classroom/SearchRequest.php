<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom;

use App\Eloquent\Staff;
use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use App\Http\Enum\StaffTeam;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom
 *
 * @property int|null $major_id
 * @property int|null $area_id
 * @property Carbon|null $first_day_of_school
 * @property int|null $staff_id
 * @property string|null $keyword
 */
class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'major_id' => Request::CAST_INT,
        'area_id' => Request::CAST_INT,
        'first_day_of_school' => Request::CAST_CARBON,
        'staff_id' => Request::CAST_INT,
        'keyword' => Request::CAST_STRING,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'major_id' => [
                'nullable',
                Rule::exists('majors', 'id'),
            ],
            'area_id' => [
                'nullable',
                Rule::exists('areas', 'id'),
            ],
            'first_day_of_school' => [
                'nullable',
                Rule::exists('enrollment_waves', 'first_day_of_school'),
            ],
            'staff_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (-1 != $value) {
                        if (0 === Staff::query()->where('team', StaffTeam::LEARNING_MANAGEMENT)->where('id', $value)->count()) {
                            return $fail('validation.exists');
                        }
                    }
                }
            ],
            'keyword' => [
                'nullable',
            ],
        ]);
    }
}
