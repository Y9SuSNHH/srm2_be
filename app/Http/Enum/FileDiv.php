<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class FileDiv extends Enum
{
    public const CONTENT_ATTACHMENT            = 1;
    public const GRADE_IMPORT                  = 2;
    public const IGNORE_LEARNING_MODULE_IMPORT = 3;
    public const STUDENT_PROFILE_IMPORT        = 4;
}
