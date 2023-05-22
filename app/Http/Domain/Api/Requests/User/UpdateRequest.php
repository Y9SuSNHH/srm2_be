<?php

namespace App\Http\Domain\Api\Requests\User;

use App\Helpers\Request;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class UpdateRequest
 * @package App\Http\Domain\Api\Requests\User
 *
 * @property string $username
 * @property string $password
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

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'username'              => [
                'required',
                'string',
                'min:3',
                Rule::unique('users', 'username')->ignore($this->httpRequest()->id)
            ],
            'password'              => 'nullable|string|min:6|confirmed',
            'password_confirmation' => 'nullable|string|min:6',
        ];
    }

    /**
     * @return array
     */
    #[ArrayShape(['updated_by' => "mixed", 'password' => "string"])]
    public function updateAttributes(): array
    {
        $attribute = ['updated_by' => $this->httpRequest()->user()->id];

        if ($this->password) {
            $attribute['password'] = \Illuminate\Support\Facades\Hash::make($this->password);
        }

        if ($this->username) {
            $attribute['username'] = $this->username;
        }

        return $attribute;
    }
}