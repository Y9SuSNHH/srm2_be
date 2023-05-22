<?php

namespace App\Http\Domain\Student\Requests\CareHistory;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

class CreateRequest extends Request
{
  /**
   * @var array
   */
  protected $casts = [
    'student_id'                      => Request::CAST_INT,
    'content'                         => Request::CAST_STRING,
    'status'                          => Request::CAST_INT,
  ];

  /**
   * @param array $input
   * @return array
   */
  public function rules(array $input): array
  {
    return [
      'student_id' => [
        'required',
        Rule::exists('students', 'id'),
      ],
      'content' => [
        'required',
      ],
      'status' => [
        'required',
      ],
    ];
  }

  public function attributes(): array
  {
    return [
      'student_id'      => 'Mã sinh viên',
      'content'         => 'Nội dung',
      'status'          => 'Trạng thái',
    ];
  }
}
