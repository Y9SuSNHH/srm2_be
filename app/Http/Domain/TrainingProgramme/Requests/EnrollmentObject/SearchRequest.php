<?php

namespace App\Http\Domain\TrainingProgramme\Requests\EnrollmentObject;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\EnrollmentObject
 *
 * @property $major_id
 * @property $keyword
 */
class SearchRequest extends Request
{
    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'major_id' => [
                'nullable',
                Rule::exists('majors', 'id')
            ],
            'keyword' => 'nullable|string'
        ];
    }
}