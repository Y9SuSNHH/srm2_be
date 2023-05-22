<?php

namespace App\Http\Domain\Api\Requests\Role;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class UpdateRequest
 * @package App\Http\Domain\Api\Requests\Role
 *
 * @property $name
 * @property $description
 * @property null|array $users
 * @property null|array $permissions
 */
class UpdateRequest extends Request
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->guard()->isPrivilege();
    }

    public function rules(array $input): array
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('roles', 'name')->ignore($this->httpRequest()->id)
            ],
            'description' => [
                'nullable',
                'string',
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
            'permissions' => [
                'nullable',
                'array'
            ],
            'permissions.*' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }
}