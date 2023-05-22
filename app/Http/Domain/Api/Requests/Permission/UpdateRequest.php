<?php

namespace App\Http\Domain\Api\Requests\Permission;

use App\Eloquent\Model;
use App\Helpers\Request;
use App\Http\Enum\PermissionAction;
use Illuminate\Validation\Rule;

/**
 * Class UpdateRequest
 * @package App\Http\Domain\Api\Requests\Permission
 *
 * @property $guard
 * @property $action
 * @property $constraint
 * @property array $roles
 * @property array $users
 */
class UpdateRequest extends Request
{
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
                Rule::unique('permissions', 'guard')->ignore($this->httpRequest()->id),
            ],
            'action' => [
                'required',
                'integer',
            ],
            'constraint' => [
                'nullable',
            ],
            'roles' => [
                'nullable',
                'array'
            ],
            'roles.*' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
            'users' => [
                'nullable',
                'array'
            ],
            'users.*' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }
}