<?php


namespace App\Http\Domain\Api\Requests\Role;


use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class CreateRequest
 * @package App\Http\Domain\Api\Requests\Role
 *
 * @property $name
 * @property $description
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

    public function rules(array $input): array
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('roles', 'name')
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }
}