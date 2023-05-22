<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class StaffTeam extends Enum
{
    const ADMISSION_ADVISER = 1; // tư vấn tuyển sinh
    const ACADEMIC_AFFAIRS_OFFICER = 2; // cán bộ công tác 
    const LEARNING_MANAGEMENT = 3; // quản lý học tập 
}
