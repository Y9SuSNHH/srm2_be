<?php

namespace App\Http\Domain\TrainingProgramme\Requests\ObjectType;

use App\Helpers\Request;

class CreateObjectTypeRequest extends Request
{
    /**
     * @var array
     */
    protected $casts = [
        'school_id'    => Request::CAST_INT,
        'name'         => Request::CAST_STRING,
        'abbreviation' => Request::CAST_STRING,
        'description'  => Request::CAST_STRING,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'school_id'    => 'required|integer',
            'abbreviation' =>'required|string',
            'name'         => 'required|string',
            'description'  => 'required|string',
        ];
    }
}