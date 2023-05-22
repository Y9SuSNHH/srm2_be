<?php

namespace App\Http\Domain\Student\Requests\Student;

use App\Helpers\Request;

/**
 * Class UpdateLearningInfoRequest
 * @package App\Http\Domain\Student\Requests\Student
 *
 * @property $account
 * @property $email
 */
class UpdateLearningInfoRequest extends Request
{
    public function rules(array $input): array
    {
        return [
            'account' => 'required|string|min:1',
            'email' => 'required|email',
        ];
    }
}
