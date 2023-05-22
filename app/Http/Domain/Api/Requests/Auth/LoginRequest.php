<?php

namespace App\Http\Domain\Api\Requests\Auth;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class LoginRequest
 * @package App\Http\Domain\Api\Requests\Auth
 *
 * @property $username
 * @property $password
 * @property $school
 */
class LoginRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'username' => 'required',
            'password' => 'required|min:6',
            'school' => [
                'nullable',
                Rule::exists('schools', 'school_code')
            ]
        ];
    }
}
