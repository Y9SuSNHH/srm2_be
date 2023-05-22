<?php

namespace App\Http\Domain\TrainingProgramme\Requests\Curriculum;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class EditCurriculumRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\Curriculum
 *
 * @property $team
 */
class EditCurriculumRequest extends Request
{
    
    /**
     * casts
     *
     * @var array
     */
    protected $casts = [
        'learning_module_id'        => Request::CAST_INT,
        'major'                     => Request::CAST_STRING,
        'curriculum_id'             => Request::CAST_INT,
    ];
    
    /**
     * rules
     *
     * @param  mixed $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'learning_module_id'        => 'required|integer',
            'objects'                   => 'required',
            'major'                     => 'required|string',
            'curriculum_id'             => 'required|integer',
        ];
    }
    
    /**
     * attributes
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'learning_module_id'        => 'ID học phần',
            'objects'                   => 'Đối tượng',
            'major'                     => 'Ngành',
            'curriculum_id'             => 'ID khung chương trình',
        ];
    }
    
    /**
     * messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'required'  => ':attribute không được bỏ trống',
            'string'   => ':attribute phải là dạng chuỗi',
            'integer'   => ':attribute phải là số'
        ];
    }
}