<?php

namespace App\Http\Domain\TrainingProgramme\Requests\Subject;

use App\Helpers\Request;

class CreateSubjectRequest extends Request
{
    /**
     * @var array
     */
    protected $casts = [
        'code'                      => Request::CAST_INT,
        'name'                      => Request::CAST_INT,
        'description'               => Request::CAST_STRING,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'code'                   => 'string',
            'name'                   => 'string',
            'description'            => 'string',
        ];
    }

}