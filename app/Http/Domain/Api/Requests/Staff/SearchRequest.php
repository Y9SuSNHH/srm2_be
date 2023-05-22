<?php

namespace App\Http\Domain\Api\Requests\Staff;

use App\Helpers\Request;
use App\Http\Enum\StaffTeam;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\Api\Requests\Staff
 *
 * @property $team
 */
class SearchRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'team' => [
                'nullable',
                Rule::in(StaffTeam::toArray())
            ]
        ];
    }
}