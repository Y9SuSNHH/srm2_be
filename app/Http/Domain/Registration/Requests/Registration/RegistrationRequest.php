<?php

namespace App\Http\Domain\Registration\Requests\Registration;

use App\Helpers\Request;

class RegistrationRequest extends Request
{
    /**
     * @var array
     */
    protected $casts = [
        'firstname'     => Request::CAST_STRING,
        'lastname'      => Request::CAST_STRING,
        'area_id'          => Request::CAST_INT,
        'phone_number'  => Request::CAST_STRING,
        'date_of_birth' => Request::CAST_CARBON,
        'email'         => Request::CAST_STRING,
        'identification'=> Request::CAST_STRING,
        'firstDay'      => Request::CAST_CARBON,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'firstname'      => 'required|string',
            'lastname'      => 'required|string',
            'area_id'     => 'required|int',
            'phone_number'   => 'required|string',
            'date_of_birth'   => 'date|before_or_equal:9999-12-31',
            'email'   => 'required|email',
            'identification'   => 'required|string',
            'firstDay'   => 'date|before_or_equal:9999-12-31',
        ];
    }

}