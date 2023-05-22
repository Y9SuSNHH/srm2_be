<?php

namespace App\Http\Domain\Api\Requests\Permission;

use App\Eloquent\Model;
use App\Helpers\Request;
use App\Http\Enum\PermissionAction;
use Illuminate\Validation\Rule;

/**
 * Class CreateRequest
 * @package App\Http\Domain\Api\Requests\Permission
 *
 * @property $guard
 * @property $action
 * @property $constraint
 */
class CreateRequest extends Request
{
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
        return [
            'guard' => [
                'required',
                Rule::in(Model::getListEloquent()),
                Rule::unique('permissions', 'guard'),
            ],
            'action' => [
                'required',
                'integer',
            ],
            'constraint' => [
                'nullable',
            ],
        ];
    }
}