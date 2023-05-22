<?php

namespace App\Http\Domain\Student\Requests\LearningProcess;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

class CreateRequest extends Request
{
  /**
   * @var array
   */
  protected $casts = [
    'learning_module_code'            => Request::CAST_STRING,
    'account'                         => Request::CAST_STRING,
    'result_btgk1'                    => Request::CAST_INT,
    'result_btgk2'                    => Request::CAST_INT,
    'result_diem_cc'                  => Request::CAST_INT,
    'deadline_btgk1'                  => Request::CAST_CARBON,
    'deadline_btgk2'                  => Request::CAST_CARBON,
    'deadline_diem_cc'                => Request::CAST_CARBON,
    'item_type'                       => Request::CAST_STRING,
  ];

  /**
   * @param array $input
   * @return array
   */
  public function rules(array $input): array
  {
    return [
      'learning_module_code'=> [
        'required',
        Rule::exists('learning_modules', 'code'),
      ],
      'account'             => [
        'required',
        Rule::exists('students', 'account'),
      ],
      'result_btgk1'        => [
        'nullable',
        'integer',
      ],
      'result_btgk2'        => [
        'nullable',
        'integer',
      ],
      'result_diem_cc'      => [
        'nullable',
        'integer',
      ],
      'deadline_btgk1'      => [
        'nullable',
        'date',
      ],
      'deadline_btgk2'      => [
        'nullable',
        'date',
      ],
      'deadline_diem_cc'    => [
        'nullable',
        'date',
      ],
      'item_type'        => [
        'nullable',
        'string',
      ],
    ];
  }

  public function attributes(): array
  {
    return [
      'learning_modules_code' => 'Mã học phần',
      'account'               => 'Tên tài khoản',
      'result_btgk1'          => 'Bài tập giữa kỳ 1',
      'result_btgk2'          => 'Bài tập giữa kỳ 2',
      'result_diem_cc'        => 'Điểm chuyên cần',
      'deadline_btgk1'        => 'Hạn nộp bài tập giữa kỳ 1',
      'deadline_btgk2'        => 'Hạn nộp bài tập giữa kỳ 2',
      'deadline_diem_cc'      => 'Hạn đánh giá điểm chuyên cần',
      'item_type'             => 'Loại bài tập',
    ];
  }
}
