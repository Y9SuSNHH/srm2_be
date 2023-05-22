<?php

namespace App\Http\Domain\TrainingProgramme\Requests\Curriculum;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class CreateCurriculumRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\Curriculum
 *
 * @property $team
 */
class CreateCurriculumRequest extends Request
{
    
    /**
     * casts
     *
     * @var array
     */
    protected $casts = [
        'major_id'                     => Request::CAST_INT,
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
            'major_id'                          => 'required|integer',
            'apply_time'                        => 'required|date|before_or_equal:9999-12-31',
            'list_learning_modules'             => 'required'
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
            'major_id'                      => 'Ngành',
            'apply_time'                    => 'Thời gian áp dụng',
            'list_learning_modules'         => 'Danh sách chương trình đào tạo'
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
            'required'      => ':attribute không được bỏ trống',
            'integer'       => ':attribute phải là dạng số',
            'date'          => ':attribute phải có định dạng là yyyy-mm-dd'
        ];
    }
}