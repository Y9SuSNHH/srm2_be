<?php

namespace App\Http\Domain\Api\Requests\School;

use App\Eloquent\School;
use App\Helpers\Request;
use App\Helpers\Rules\Unique;
use App\Http\Enum\SchoolTheme;
use Illuminate\Validation\Rule;

/**
 * Class CreateSchoolRequest
 * @package App\Http\Domain\Api\Requests\School
 */
class CreateSchoolRequest extends Request
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
                new Unique(School::class, 'school_code'),
            ],
            'school_name' => 'required|string|max:150',
            'service_name' => 'nullable|string',
            'school_status' => 'nullable|integer',
            'priority' => 'nullable|integer',
            'theme' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(SchoolTheme::toArray())
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'school_code' => 'Mã trường',
            'school_name' => 'Tên trường',
            'theme' => 'Chủ đề',
            'priority' => 'Ưu tiên',
        ];
    }
}
