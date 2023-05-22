<?php

namespace App\Http\Domain\Reports\Requests\G120;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\Reports\Requests\G120
 *
 * @property $team
 */
class SearchRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'first_day_of_school'   => 'nullable|date|before_or_equal:9999-12-31',
            'area'                  => 'nullable|string',
            'major'                 => 'nullable|string',
            'staff'                 => 'nullable|integer',
            'classes'               => 'nullable',
            'keywords'              => 'nullable|string',
        ];
    }

    /**
     * attributes
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'staff'                 => 'QLHT',
        ];
    }
}