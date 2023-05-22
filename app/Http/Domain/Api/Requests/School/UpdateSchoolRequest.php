<?php

namespace App\Http\Domain\Api\Requests\School;

use App\Helpers\Request;
use App\Http\Enum\SchoolTheme;
use Illuminate\Validation\Rule;

/**
 * Class CreateSchoolRequest
 * @package App\Http\Domain\Api\Requests\School
 */
class UpdateSchoolRequest extends Request
{
    /**
     * @param array $input
     * @return array[]
     * @throws \ReflectionException
     */
    public function rules(array $input): array
    {
        return [
            'school_code' => [
                'required',
                'alpha_dash',
                'max:50',
                Rule::exists('schools', 'school_code'),
            ],
            'priority' => 'nullable|integer',
            'theme' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(SchoolTheme::toArray())
            ],
            'school_name' => 'required|string|max:150',
            'service_name' => 'required|string',
            'school_status' => 'nullable|integer',
        ];
    }
}
