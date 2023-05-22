<?php

namespace App\Http\Domain\Student\Models\CareHistory;

use App\Helpers\Json;

class CareHistory extends Json
{
    public $id;
    public $student_id;
    public $content;
    public $status;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;

}