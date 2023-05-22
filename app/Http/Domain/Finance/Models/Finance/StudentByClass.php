<?php

namespace App\Http\Domain\Finance\Models\Finance;

use App\Eloquent\Classroom as ClassroomModel;
use App\Helpers\Json;

class StudentByClass extends Json
{ 
    public $studentInfo;
    public $staff;
    public $area;
    public $semester;

    public function __construct(ClassroomModel $classroom)
    {        
        parent::__construct(array_merge($classroom->toArray(), [
            'staff' => ($classroom->staff) ? $classroom->staff->fullname: null,
            'area' => $classroom->area->code,
            'studentInfo' => $classroom->students,
            'semester' => $classroom->period,
        ]));
    }
}