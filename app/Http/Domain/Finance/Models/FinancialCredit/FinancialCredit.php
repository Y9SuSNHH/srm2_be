<?php

namespace App\Http\Domain\Finance\Models\FinancialCredit;

use App\Helpers\Json;

class FinancialCredit extends Json
{ 
    public $id;
    public $student_profile_id;
    public $transaction_id;
    public $amount;
    public $purpose;
    public $no;
    public $note;
    public $created_at;
    public $created_by;
    public $deleted_time;
    public $deleted_by;
}