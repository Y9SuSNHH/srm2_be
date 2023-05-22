<?php

namespace App\Http\Domain\TrainingProgramme\Requests\MajorObjectMap;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\MajorObjectMap
 *
 * @property int|null $major_id
 * @property int|null $enrollment_object_id
 */
class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'major_id' => Request::CAST_INT,
        'enrollment_object_id' => Request::CAST_INT,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'major_id' => [
                'nullable',
                Rule::exists('majors', 'id'),
            ],
            'enrollment_object_id' => [
                'nullable',
                Rule::exists('enrollment_objects', 'id'),
            ],
        ]);
    }
}
