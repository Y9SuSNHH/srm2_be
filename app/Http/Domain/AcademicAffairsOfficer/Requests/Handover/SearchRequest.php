<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Handover;

use App\Eloquent\Area;
use App\Eloquent\Handover;
use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'area_id'             => Request::CAST_INT,
        'first_day_of_school' => Request::CAST_CARBON,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'area_id'             => [
                'nullable',
                'integer',
                'min:1',
                Rule::exists(Area::class, 'id')
            ],
            'first_day_of_school' => [
                'nullable',
                'date_format:Y-m-d',
            ],
        ]);
    }

}