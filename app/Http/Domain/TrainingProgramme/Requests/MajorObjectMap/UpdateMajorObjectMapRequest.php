<?php

namespace App\Http\Domain\TrainingProgramme\Requests\MajorObjectMap;

use App\Helpers\Request;
use Illuminate\Validation\Rule;
use App\Helpers\Rules\Unique;
use App\Eloquent\MajorObjectMap;

class UpdateMajorObjectMapRequest extends Request
{
    /**
     * @var array
     */
    protected $casts = [
        'major_id'                       => Request::CAST_INT,
        'enrollment_object_id'           => Request::CAST_INT,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'major_id'                       => [
                'required',
                Rule::exists('majors', 'id'),
            ],
            'enrollment_object_id'           => [
                'required',
                function ($attribute, $value, $fail) {
                    $query = MajorObjectMap::query()
                        ->where('enrollment_object_id', $this->httpRequest()->get('enrollment_object_id'))
                        ->where('major_id', $this->httpRequest()->get('major_id'))
                        ->where('id', '<>', $this->httpRequest()->id)
                        ->count();
                    if ($query > 0) {
                        return $fail('Ngành học này đã tồn tại');
                    }
                },
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'major_id' => 'Mã ngành',
            'enrollment_object_id' => 'Mã đối tượng',
        ];
    }
}