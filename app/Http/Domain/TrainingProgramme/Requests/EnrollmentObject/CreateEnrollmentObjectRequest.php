<?php

namespace App\Http\Domain\TrainingProgramme\Requests\EnrollmentObject;

use App\Eloquent\EnrollmentObject;
use App\Helpers\Request;
use App\Helpers\Rules\Unique;
use App\Http\Enum\ObjectClassification;
use Illuminate\Validation\Rule;;

class CreateEnrollmentObjectRequest extends Request
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
     * @throws \ReflectionException
     */
    public function rules(array $input): array
    {
        $classification = $input['classification'] ?? '';

        return [
            'code' => [
                'required',
                function ($attribute, $value, $fail) use ($input, $classification) {
                    $count = EnrollmentObject::query()
                        ->where('code', $value)
                        ->where('classification', $classification)
                        ->where('school_id', school()->getId())
                        ->where('id', '<>', $this->httpRequest()->id)
                        ->count();
                    if ($count > 0) {
                        return $fail('Mã đối tượng đã tồn tại');
                    }
                },
                // (new Unique(EnrollmentObject::class, 'code'))->where('classification', $classification)->ignore($this->httpRequest()->id)
            ],
            'classification' => [
                Rule::in(ObjectClassification::toArray())
            ],
            'name' => 'required|string',
            'shortcode' => [
                'required',
                function ($attribute, $value, $fail) use ($input, $classification) {
                    $count = EnrollmentObject::query()
                        ->where('shortcode', $value)
                        ->where('school_id', school()->getId())
                        ->where('id', '<>', $this->httpRequest()->id)
                        ->count();
                    if ($count > 0) {
                        return $fail('Mã viết tắt đối tượng đã tồn tại');
                    }
                },
                // (new Unique(EnrollmentObject::class, 'shortcode'))->ignore($this->httpRequest()->id)
            ],
        ];
    }
    public function attributes(): array
    {
        return [
            'code'                    => 'Mã đối tượng',
            'classification'          => 'Loại đối tượng',
            'name'                    => 'Tên đối tượng',
            'shortcode'               => 'Mã viết tắt đối tượng',
        ];
    }
}