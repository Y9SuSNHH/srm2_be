<?php

namespace App\Http\Domain\Api\Requests\User;

use App\Helpers\Request;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class CreateRequest
 * @package App\Http\Domain\Api\Requests\Area
 *
 * @property string $username
 * @property string $password
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
     * @var array
     */
    protected $casts = [
        'school_id' => Request::CAST_INT,
        'code'      => Request::CAST_STRING,
        'name'      => Request::CAST_STRING,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'username'              => 'required|string|unique:App\Eloquent\User|min:3',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ];
    }

    /**
     * @return array
     */
    #[ArrayShape(['username' => "string", 'password' => "string", 'created_by' => "mixed"])]
    public function createAttributes(): array
    {
        return [
            'username' => $this->username,
            'password' => \Illuminate\Support\Facades\Hash::make($this->password),
            'created_by' => $this->httpRequest()->user()->id,
        ];
    }
}
