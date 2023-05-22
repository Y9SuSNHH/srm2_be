<?php

namespace App\Http\Domain\Finance\Models\Transaction;

use App\Helpers\Json;

class Transaction extends Json
{
    public $id;
    public $student_profile_id;
    public $code;
    public $is_debt;
    public $amount;
    public $note;
    public $approval_status;
    public $created_at;
    public $created_by;
    public $deleted_time;
    public $deleted_by;

}