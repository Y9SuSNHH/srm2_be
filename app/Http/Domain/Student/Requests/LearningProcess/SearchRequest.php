<?php

namespace App\Http\Domain\Student\Requests\LearningProcess;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\Student\Requests\LearningProcess
 *
 * @property string|null $classroom_id
 * @property string|null $profile_code
 * @property string|null $student_code
 * @property string|null $fullname
 * @property string|null $btgk1_status
 * @property string|null $btgk2_status
 * @property string|null $diem_cc_status
 * @property string|null $tuition_status
 * @property string|null $petition_status
 */
class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
      'classroom_id'                    => Request::CAST_STRING,
      'profile_code'                    => Request::CAST_STRING,
      'student_code'                    => Request::CAST_STRING,
      'fullname'                        => Request::CAST_STRING,
      'btgk1_status'                    => Request::CAST_STRING,
      'btgk2_status'                    => Request::CAST_STRING,
      'diem_cc_status'                  => Request::CAST_STRING,
      'tuition_status'                  => Request::CAST_STRING,
      'petition_status'                 => Request::CAST_STRING,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'classroom_id' => [
                'nullable',
            ],
            'profile_code' => [
                'nullable',
            ],
            'student_code' => [
                'nullable',
            ],
            'fullname' => [
                'nullable',
            ],
            'btgk1_status' => [
                'nullable',
            ],
            'btgk2_status' => [
                'nullable',
            ],
            'diem_cc_status' => [
                'nullable',
            ],
            'tuition_status' => [
                'nullable',
            ],
            'petition_status' => [
                'nullable',
            ],
        ]);
    }
}
