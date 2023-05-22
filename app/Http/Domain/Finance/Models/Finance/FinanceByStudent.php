<?php

namespace App\Http\Domain\Finance\Models\Finance;

use App\Eloquent\finance as financeModel;
use App\Eloquent\FinancialCredit as FinancialCreditModel;
use App\Eloquent\Staff;
use App\Helpers\Json;
use App\Http\Enum\StudentReceivablePurpose;

class FinanceByStudent extends Json
{
    public $id;
    public $fullname;
    public $dob;
    public $profileCode;
    public $studentCode;
    public $semester;
    public $purpose;
    public $amount;
    public $received;
    public $totalReceived;
    public $price;
    public $classroom;
    public $study_plans;
    public $ignore;
    public $note;
    public $action;

    public function __construct(FinancialCreditModel $finance, int $price)
    {
        $staff = Staff::where('user_id', auth()->getId())->first();
        $study_plans = $finance->student->classroom->studyPlans->where('semester', $finance->no);
        $ignoreArr = $finance->student->ignoreLearningModules->pluck('learning_module_id')->toArray();

        // foreach ($ignoreArr as $ignore) {
        //     if ($finance->student->id === $ignore['student_id']) {
        //         foreach ($study_plans as $study_plan) {
        //             if ($ignore['learning_module_id'] === $study_plan->learning_module_id) {
        //                 $study_plan->calc_credit = 0;
        //             }
        //         }
        //     }
        // }

        parent::__construct(array_merge($finance->toArray(), [
            'id' => $finance->id,
            'fullname' => $finance->studentProfile->getProfile->firstname . ' ' . $finance->studentProfile->getProfile->lastname,
            'dob' => date('d/m/Y', strtotime($finance->studentProfile->getProfile->birthday)),
            'profileCode' => $finance->studentProfile->profile_code,
            'studentCode' => $finance->student->student_code,
            'semester' => $finance->no,
            'purpose' => StudentReceivablePurpose::getValueByKey($finance->purpose),
            'amount' => $finance->amount,
            'received' => $finance->amountsReceived,
            'totalReceived' => $finance->totalReceived,
            'classroom' => $finance->student->classroom,
            'price' => $price,
            'study_plans' => $study_plans,
            'ignore' => $ignoreArr,
            'note' => $finance->note,
            'action' => ($finance->student->classroom->staff_id === $staff->id) ? true : false
        ]));
    }
}
