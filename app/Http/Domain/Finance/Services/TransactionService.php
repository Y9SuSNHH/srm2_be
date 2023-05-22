<?php

namespace App\Http\Domain\Finance\Services;

use App\Http\Domain\Finance\Repositories\Transaction\TransactionRepositoryInterface;
use App\Http\Domain\Finance\Repositories\FinancialCredit\FinancialCreditRepositoryInterface;
use App\Http\Enum\ApprovalStatus;
use App\Http\Enum\ReceivablePurpose;
use App\Http\Enum\StudentReceivablePurpose;
use Carbon\Carbon;
use App\Eloquent\FinancialCredit;
use Illuminate\Support\Facades\DB;

class TransactionService
{
  /**
   * transaction_repository
   *
   * @var mixed
   */
  private $transaction_repository;
  private $financial_credit_repository;
  /**
   * __construct
   *
   * @param  mixed $transaction_repository
   * @return void
   */
  public function __construct(TransactionRepositoryInterface $transaction, FinancialCreditRepositoryInterface $financial_credit)
  {
    $this->transaction_repository = $transaction;
    $this->financial_credit_repository = $financial_credit;
  }

  /**
   * store
   *
   * @return void
   */
  public function dataInsert()
  {
    $ignore_learning_modules = $this->transaction_repository->getIgnoreLearningModule();
    $amount_credits = $this->transaction_repository->getSumAmountCreditByClass();
    $students = $this->transaction_repository->getStudentsByClass();
    $credit_price = $this->transaction_repository->getCreditPrices();

    $data = array();
    foreach ($students as $student) {
      $amount_credit = $amount_credits->where('classroom_id', $student->classroom_id)->all();
      if ($amount_credit) {
        // // History

        // foreach ($amount_credit as $ac) {
        //   if (
        //     ($ac->decision_date == null && $student->sc_began_at <= $ac->collect_began_date && ($student->sc_ended_at >= $ac->collect_began_date || $student->sc_ended_at == null))
        //     ||
        //     ($ac->decision_date != null && $student->sc_began_at <= $ac->decision_date && ($student->sc_ended_at >= $ac->decision_date || $student->sc_ended_at == null))
        //   ) {
        //     if (
        //       $student->value == 8 &&
        //       (
        //         ($ac->decision_date == null && $student->began_at <= $ac->collect_began_date && ($student->ended_at >= $ac->collect_began_date || $student->ended_at == null))
        //         ||
        //         ($ac->decision_date != null && $student->began_at <= $ac->decision_date && ($student->ended_at >= $ac->decision_date || $student->ended_at == null))
        //       )
        //     ) {
        //       $student_profile_id = $student->student_profile_id ?? null;
        //       $classroom_id = $student->classroom_id;
        //       $semester = $ac->semester ?? null;
        //       $amount_credit_total = $ac->sum_amount_credit ?? 0;
        //       $first_day_of_school = $ac->first_day_of_school ?? null;
        //       // $collect_began_date = $ac->collect_began_date ?? null;
        //       $now = Carbon::now();
        //       $credit = $credit_price->where('effective_date', '<=', $first_day_of_school)->first()
        //         ? $credit_price->where('effective_date', '<=', $first_day_of_school)->first()->price
        //         : 0;
        //       $ignore_amount_credit = $ignore_learning_modules->where('student_profile_id', $student_profile_id)
        //         ->where('classroom_id', $classroom_id)
        //         ->where('semester', $semester)
        //         ->where('created_at', '<=', $now)
        //         ->sum('amount_credit');
        //       $item['student_profile_id'] = $student_profile_id;
        //       $item['amount'] = - (($amount_credit_total - $ignore_amount_credit) * $credit);
        //       if ($ac->decision_date && strtotime(str_replace('/', '', $ac->decision_date))) {
        //         $decision_date = date('Ymd', strtotime(str_replace('/', '', $ac->decision_date)));
        //       } else {
        //         $decision_date = null;
        //       }
        //       $receivable_purpose = ReceivablePurpose::TUITION_FEE;
        //       $purpose = StudentReceivablePurpose::getValueByKey($receivable_purpose);
        //       $item['code'] = "'" . 'A' . '.' . $student_profile_id . '.' . $purpose . '.' . $semester . '.' . $decision_date . "'";
        //       $item['approval_status'] = ApprovalStatus::IN_PROCESS;
        //       $item['note'] = 'null';
        //       $item['is_debt'] = 'true';
        //       $now = Carbon::now()->toDateTimeString();
        //       $item['created_at'] = "'$now'::TIMESTAMP";
        //       $item['created_by'] = auth()->getId();
        //       $item['deleted_time'] = 0;
        //       if ($item['student_profile_id'] != null && $item['code'] != null && isset($item['amount']) && isset($item['approval_status'])) {
        //         $data[] = $item;
        //       }
        //     }
        //   }
        // }

        // Daily 
        foreach ($amount_credit as $ac) {
          $student_profile_id = $student->student_profile_id ?? null;
          $classroom_id = $student->classroom_id;
          $semester = $ac->semester ?? null;
          $amount_credit_total = $ac->sum_amount_credit ?? 0;
          $first_day_of_school = $ac->first_day_of_school ?? null;
          // $collect_began_date = $ac->collect_began_date ?? null;
          $now = Carbon::now();
          $credit = $credit_price->where('effective_date', '<=', $first_day_of_school)->first()
            ? $credit_price->where('effective_date', '<=', $first_day_of_school)->first()->price
            : 0;
          $ignore_amount_credit = $ignore_learning_modules->where('student_profile_id', $student_profile_id)
            ->where('classroom_id', $classroom_id)
            ->where('semester', $semester)
            ->where('created_at', '<=', $now)
            ->sum('amount_credit');
          $item['student_profile_id'] = $student_profile_id;
          $item['amount'] = - (($amount_credit_total - $ignore_amount_credit) * $credit);
          if ($ac->decision_date && strtotime(str_replace('/', '', $ac->decision_date))) {
            $decision_date = date('Ymd', strtotime(str_replace('/', '', $ac->decision_date)));
          } else {
            $decision_date = null;
          }
          $receivable_purpose = ReceivablePurpose::TUITION_FEE;
          $purpose = StudentReceivablePurpose::getValueByKey($receivable_purpose);
          $item['code'] = "'" . 'A' . '.' . $student_profile_id . '.' . $purpose . '.' . $semester . '.' . $decision_date . "'";
          $item['approval_status'] = ApprovalStatus::IN_PROCESS;
          $item['note'] = 'null';
          $item['is_debt'] = 'true';
          $now = Carbon::now()->toDateTimeString();
          $item['created_at'] = "'$now'::TIMESTAMP";
          $item['created_by'] = auth()->getId();
          $item['deleted_time'] = 0;
          if ($item['student_profile_id'] != null && $item['code'] != null && isset($item['amount']) && isset($item['approval_status'])) {
            $data[] = $item;
          }
        }
      }
    };

    return $data;
  }

  public function insertData($attributes)
  {
    $transaction_query = $this->queryTransaction($attributes);
    $this->transaction_repository->insert($transaction_query);

    $code = trim($attributes['code'], "'");
    $fc_attributes = $this->transaction_repository->getTransactionByCode($code);
    $financial_credit_query = $this->queryFinancialCredit($fc_attributes);
    $this->transaction_repository->insert($financial_credit_query);
  }

  public function queryTransaction($attributes)
  {
    $attribute[] = $attributes;
    $values = "(" . implode('), (', array_map(function ($array) {
      return implode(', ', $array);
    }, $attribute)) . ")";
    $query = "insert into transactions (student_profile_id, amount, code, approval_status, note, is_debt, created_at, created_by, deleted_time) 
      values $values 
      on conflict (code, is_debt, deleted_time) 
      do update set " .
      'student_profile_id=excluded.student_profile_id,' .
      'amount=excluded.amount,' .
      'code=excluded.code,' .
      'approval_status=excluded.approval_status,' .
      'note=excluded.note,' .
      'is_debt=excluded.is_debt,' .
      'deleted_time=excluded.deleted_time ';
    return $query;
  }

  public function queryFinancialCredit($attributes)
  {
    $data = array();
    $item['student_profile_id'] = $attributes->student_profile_id;
    $item['transaction_id'] = $attributes->id;
    $item['amount'] = - ($attributes->amount);
    $code = $attributes->code;
    $arr = explode(".", $code);
    $item['purpose'] = StudentReceivablePurpose::getValueByKeyVi($arr[2]);
    $item['no'] = $arr[3];
    $item['note'] = 'null';
    $now = Carbon::now()->toDateTimeString();
    $item['created_at'] = "'$now'::TIMESTAMP";
    $item['created_by'] = $attributes->created_by;
    $item['deleted_time'] = 0;
    $data[] = $item;

    $values = "(" . implode('), (', array_map(function ($array) {
      return implode(', ', $array);
    }, $data)) . ")";
    $query = "insert into financial_credits (student_profile_id, transaction_id, amount, purpose, no, note, created_at, created_by, deleted_time) 
      values $values 
      on conflict (student_profile_id, purpose, no, deleted_time) 
      do update set " .
      'student_profile_id=excluded.student_profile_id,' .
      'transaction_id=excluded.transaction_id,' .
      'amount=excluded.amount,' .
      'purpose=excluded.purpose,' .
      'no=excluded.no,' .
      'note=excluded.note,' .
      'deleted_time=excluded.deleted_time ';
    return $query;
  }

  public function add($attributes)
  {
    $now = Carbon::now();
    foreach ($attributes->params as $key => $value) {
      $item = array();
      $item['student_profile_id'] = $value['student_profile_id'];
      $item['code'] = $value['code'];
      $item['amount'] = - ($value['amount']);
      $item['note'] = (!empty($value['note'])) ? $value['note'] : null;
      $item['is_debt'] = 'true';
      $item['approval_status'] = ApprovalStatus::IN_PROCESS;
      $item['created_by'] = auth()->getId();
      $item['created_at'] = $now;
      $item['deleted_time'] = 0;
      DB::transaction(function () use ($item, $now) {
        $transaction_id = $this->transaction_repository->create($item);
        $code = $item['code'];
        $arr_code = explode(".", $code);
        $financial_credit = [
          'student_profile_id' => $item['student_profile_id'],
          'transaction_id' => $transaction_id,
          'amount' => - ($item['amount']),
          'purpose' => $arr_code[2] ? StudentReceivablePurpose::getValueByKeyVi($arr_code[2]) : 0,
          'no' => $arr_code[3] ?? 0,
          'note' => $item['note'],
          'created_at' => $now,
          'created_by' => auth()->getId(),
          'deleted_time' => 0,
        ];
        $this->financial_credit_repository->create($financial_credit);
      });
    }
  }

  public function insertStudentReceivable()
  {
    $query = "insert into student_receivables (student_profile_id, receivable, purpose, learning_wave_number, reference_id, reference_table)
                select student_profile_id, amount, purpose, no::int, id, 'financial_credits' from financial_credits
                on conflict (student_profile_id, purpose, learning_wave_number, reference_id, reference_table) 
                do update 
                set receivable=excluded.receivable";
    return $this->transaction_repository->insert($query);
  }
}
