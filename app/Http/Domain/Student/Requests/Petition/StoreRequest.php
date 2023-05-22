<?php

namespace App\Http\Domain\Student\Requests\Petition;

use App\Eloquent\Area;
use App\Eloquent\Classroom;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use App\Helpers\Request;
use App\Http\Enum\PetitionContentType;
use App\Http\Enum\StudentStatus;
use Illuminate\Validation\Rule;
use ReflectionException;

class StoreRequest extends Request
{
    protected $casts = [
        'petition_id'   => Request::CAST_INT,
        'new_content'    => Request::CAST_ARRAY,
//        'effective_date' => Request::CAST_CARBON,
        'note'           => Request::CAST_STRING,
    ];

    /**
     * @param array $input
     * @return array
     * @throws ReflectionException
     */
    public function rules(array $input): array
    {
        $content_type = $input['content_type'];

        $classroom      = [
            'required',
            'numeric',
            Rule::exists(Classroom::class, 'id')
        ];
        $student_status = [
            'required',
            'numeric',
            Rule::in(array_values(StudentStatus::toArray())),
        ];
        $area           = [
            'required',
            'numeric',
            Rule::exists(Area::class, 'id'),
        ];

        $add_rules = [];
        if (in_array($content_type, PetitionContentType::classroom())) {
            $add_rules['new_content.classroom'] = $classroom;
        }
//        if (in_array($content_type, PetitionContentType::student())) {
//            $add_rules['new_content.student_status'] = $student_status;
//        }
        if (in_array($content_type, PetitionContentType::area())) {
            $add_rules['new_content.classroom'] = $classroom;
            $add_rules['new_content.area']      = $area;
        }

        $rules = [
            'content_type'   => [
                'required',
                Rule::in(array_values(PetitionContentType::toArray())),
            ],
            'new_content'    => [
                'array',
            ],
//            'effective_date' => [
//                'date_format:Y-m-d'
//            ],
            'note'           => [
                'string'
            ],
        ];

        return array_merge($rules, $add_rules);
    }


    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'content_type'          => 'loại đơn từ',
            'new_content'           => 'nội dung thay đổi',
            'new_content.classroom' => 'lớp chuyển đến',
            'new_content.area'      => 'trạm chuyển đên',
            'effective_date'        => 'ngày có hiệu lưc',
            'note'                  => 'ghi chú',
        ];
    }
}