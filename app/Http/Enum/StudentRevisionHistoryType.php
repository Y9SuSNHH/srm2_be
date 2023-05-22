<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class StudentRevisionHistoryType extends Enum
{
    public const CLASSROOM = 1;
    public const STUDENT_STATUS = 2;
    public const PROFILE_STATUS = 3;
    public const DECISION_NO = 4;
    public const DECISION_DATE = 5;
    public const REQUISITION_DECISION_NO = 6;
    public const REQUISITION_DECISION_DATE = 7;
}
