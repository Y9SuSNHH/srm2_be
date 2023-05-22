<?php

namespace App\Http\Domain\Student\Requests\Student;

use App\Helpers\Request;
use App\Http\Enum\StudentStatus;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class ApproveStudentUpdateRequest
 * @package App\Http\Domain\Student\Requests\Student
 *
 * @property string $description
 * @property int $decision_no
 * @property \Carbon\Carbon $decision_date
 * @property \Carbon\Carbon $decision_return_date
 * @property string $student_code
 * @property int $classroom_id
 * @property int $student_status
 */
class ApproveStudentUpdateRequest extends Request
{
    protected $casts = [
        'decision_no' => Request::CAST_INT,
        'decision_date' => Request::CAST_CARBON,
        'decision_return_date' => Request::CAST_CARBON,
        'student_code' => Request::CAST_STRING,
        'student_status' => Request::CAST_INT,
        'classroom_id' => Request::CAST_INT,
    ];

    public function rules(array $input): array
    {
        return [
            'description' => [
                'nullable',
            ],
            'decision_no' => [
                'nullable',
                'integer'
            ],
            'decision_date' => [
                'nullable',
                'date'
            ],
            'decision_return_date' => [
                'nullable',
                'date'
            ],
            'student_code' => [
                'nullable',
            ],
            'classroom_id' => [
                'nullable',
                Rule::exists('classrooms', 'id'),
            ],
            'student_status' => [
                'nullable',
                Rule::in(StudentStatus::toArray()),
            ],
        ];
    }

    /**
     * @return array
     */
    #[ArrayShape(['decision_no' => "int", 'decision_date' => "\Carbon\Carbon", 'decision_return_date' => "\Carbon\Carbon", 'student_code' => "string", 'account' => "string", 'email' => "string", 'student_status' => "int"])]
    public function getApprovalItems(): array
    {
        return [
            'decision_no' => $this->__get('decision_no'),
            'decision_date' => $this->__get('decision_date'),
            'decision_return_date' => $this->__get('decision_return_date'),
            'student_code' => $this->__get('student_code'),
            'classroom_id' => $this->__get('classroom_id'),
            'student_status' => $this->__get('student_status'),
        ];
    }
}
