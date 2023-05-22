<?php

namespace App\Http\Domain\Workflow\Model\Workflow;

use App\Helpers\Json;
use App\Http\Enum\StudentStatus;

class Student extends Json
{
    public $student_status;
    public $student_status_name;
    public $student_code;
    public $decision_no;
    public $decision_date;
    public $decision_return_date;
    public $classroom_id;
    public $classroom_code;

    public function __construct($argument = null)
    {
        /** @var \App\Eloquent\Student $argument */
        $documents = json_decode($argument->studentProfile->documents ?? '', true);
        /** @var \App\Eloquent\Classroom|null $classroom */
        $classroom = $argument->classrooms->first();
        parent::__construct([
            'student_status' => $argument->student_status,
            'student_status_name' => StudentStatus::fromOptional($argument->student_status)->getLang(),
            'student_code' => $argument->student_code,
            'decision_no' => !$documents ? null : array_get($documents, 'decision_no'),
            'decision_date' => !$documents ? null : array_get($documents, 'decision_date'),
            'decision_return_date' => !$documents ? null : array_get($documents, 'decision_return_date'),
            'classroom_id' => !$classroom ? null : $classroom->id,
            'classroom_code' => !$classroom ? null : $classroom->code,
        ]);
    }
}
