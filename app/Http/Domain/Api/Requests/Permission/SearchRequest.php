<?php

namespace App\Http\Domain\Api\Requests\Permission;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use App\Http\Enum\PermissionAction;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\Api\Requests\EnrollmentWave
 *
 * @property int|null $action
 */
class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'area_id' => Request::CAST_INT,
        'first_day_of_school' => Request::CAST_CARBON,
        'school_id' => Request::CAST_INT,
    ];

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->guard()->isPrivilege();
    }

    /**
     * @param array $input
     * @return array
     * @throws \ReflectionException
     */
    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'action' => [
                'nullable',
                Rule::in(PermissionAction::toArray()),
            ],
        ]);
    }
}
