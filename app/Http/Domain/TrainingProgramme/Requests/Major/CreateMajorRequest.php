<?php

namespace App\Http\Domain\TrainingProgramme\Requests\Major;

use App\Eloquent\Major;
use App\Helpers\Request;
use Illuminate\Validation\Rule;

class CreateMajorRequest extends Request
{
    /**
     * @var array
     */
    protected $casts = [
        'school_id' => Request::CAST_INT,
        'code'      => Request::CAST_STRING,
        'name'      => Request::CAST_STRING,
        'shortcode' => Request::CAST_STRING,
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
                function ($attribute, $value, $fail) use ($input) {
                    $query = Major::query()
                        ->where('code', $value)
                        ->where('school_id', school()->getId())
                        ->where('id', '<>', $this->httpRequest()->id);
                    $count = !empty($input['area_id']) ? $query->where('area_id', $input['area_id'])->count() : $query->count();
                    if ($count > 0) {
                        return $fail('Ngành đã tồn tại');
                    }
                }
            ],
            'name'      => ['required'],
            'shortcode' => ['required'],
        ];
    }
    public function attributes(): array
    {
        return [
            'code'          => 'Mã ngành',
            'name'          => 'Tên ngành',
            'shortcode'     => 'Mã viết tắt ngành',
        ];
    }
}
