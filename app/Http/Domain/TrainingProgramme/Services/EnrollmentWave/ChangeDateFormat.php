<?php

namespace App\Http\Domain\TrainingProgramme\Services\EnrollmentWave;

use App\Helpers\Request;
use Carbon\Carbon;

class ChangeDateFormat
{
    public function handle(array $validator) :array
    {
        $validator['first_day_of_school'] = Carbon::parse($validator['first_day_of_school'])->toDateString('Y-m-d');
//        if(empty($validator['enrollment_start_date']))
//        {
//            $validator['enrollment_start_date'] = Carbon::parse($validator['first_day_of_school'])->subDays(42)->toDateString('Y-m-d');
//        }
//        if(empty($validator['enrollment_end_date']))
//        {
//            $validator['enrollment_end_date'] = Carbon::parse($validator['first_day_of_school'])->toDateString('Y-m-d');
//        }
        if(!empty($validator['enrollment_start_date']))
        {
            $validator['enrollment_start_date'] = Carbon::parse($validator['enrollment_start_date'])->toDateString('Y-m-d');
        }
        if(!empty($validator['enrollment_end_date']))
        {
            $validator['enrollment_end_date'] = Carbon::parse($validator['enrollment_end_date'])->toDateString('Y-m-d');
        }
        if(empty($validator['application_submission_deadline']))
        {
            $validator['application_submission_deadline'] = Carbon::parse($validator['first_day_of_school'])->subDays(1)->toDateString('Y-m-d');
        }
        if(empty($validator['tuition_payment_deadline']))
        {
            $validator['tuition_payment_deadline'] = Carbon::parse($validator['first_day_of_school'])->addDays(1)->toDateString('Y-m-d');
        }
        return $validator;
    }
}