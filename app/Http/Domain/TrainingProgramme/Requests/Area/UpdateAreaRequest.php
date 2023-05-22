<?php

namespace App\Http\Domain\TrainingProgramme\Requests\Area;

use App\Eloquent\Area;
use App\Helpers\Request;
use App\Helpers\Rules\Unique;

class UpdateAreaRequest extends Request
{
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
            'code'      => [
                'required',
                'string',
                (new Unique(Area::class, 'code'))->ignore($this->httpRequest()->id)
            ],
            'name'      =>'required|string',
        ];
    }
}
