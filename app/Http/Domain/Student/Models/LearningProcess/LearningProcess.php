<?php

namespace App\Http\Domain\Student\Models\LearningProcess;

use App\Helpers\Json;

class LearningProcess extends Json
{
    public $id;
    public $learning_modules_id;
    public $student_id;
    public $result_btgk1;
    public $result_btgk2;
    public $result_diem_cc;
    public $deadline_btgk1;
    public $deadline_btgk2;
    public $deadline_diem_cc;
    public $item_type;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
}
