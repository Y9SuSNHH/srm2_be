<?php

namespace App\Http\Domain\TrainingProgramme\Models\Period;

use App\Helpers\Json;
use App\Eloquent\Period as EloquentPeriod;

class Period extends Json
{
    public $id;
    public $classroom_id;
    public $classroom_code;
    public $semester;
    public $decision_date; 
    public $collect_began_date;
    public $collect_ended_date;
    public $learn_began_date;
    public $expired_date_com;
    public $learn_ended_date;
    public $is_final;

    public function __construct(EloquentPeriod $period)
    {
        parent::__construct(array_merge($period->toArray(), [
            'classroom_code' => $period->classroom->code ?? null,
        ]));
    }
}