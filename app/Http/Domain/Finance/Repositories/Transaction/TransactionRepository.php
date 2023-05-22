<?php

namespace App\Http\Domain\Finance\Repositories\Transaction;

use App\Eloquent\FinancialCredit;
use App\Http\Domain\Finance\Repositories\Transaction\TransactionRepositoryInterface;
use App\Eloquent\Transaction;
use App\Http\Domain\Finance\Models\Transaction\Transaction as TransactionModel;
use App\Http\Enum\ApprovalStatus;
use App\Http\Enum\StudentReceivablePurpose;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;
use App\Helpers\Traits\ThrowIfNotAble;

/**
 * Class TransactionRepository
 * @package App\Http\Domain\Receivable\Repositories\Receivable
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    use ThrowIfNotAble;

    private $model;
    public function __construct()
    {
        $this->model = Transaction::query()->getModel();
    }

    public function getTransactionByCode(string $code)
    {
        $results = DB::table('transactions')->where('code', $code)->first();
        return $results;
    }

    /**
     * @param array $validator
     * @return mixed
     */
    public function create(array $validator): mixed
    {
        try {
            return $this->model->insertGetId($validator);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    public function insert($sql)
    {
        DB::statement($sql);
    }

    public function getSumAmountCreditByClass()
    {

        // Job daily
        // WHERE ((semester = 1  AND collect_began_date = NOW()) OR (decision_date is not null AND decision_date = NOW()))

        // History
        // WHERE ((semester = 1  AND collect_began_date < NOW()) OR (decision_date is not null AND decision_date < NOW()))
        $results = DB::table('learning_modules AS lm')
        ->join('study_plans AS sp', 'lm.id', '=', 'sp.learning_module_id')
        ->join(DB::raw('(SELECT c.id AS classroom_id, semester, decision_date, collect_began_date, ew.first_day_of_school FROM periods AS p
                         JOIN classrooms AS c ON c.id = p.classroom_id 
                         JOIN enrollment_waves AS ew ON c.enrollment_wave_id = ew.id
                         WHERE (c.deleted_time IS NULL OR c.deleted_time=0)) AS cs'), 
                         function ($join) {
                            $join->on('sp.classroom_id', '=', 'cs.classroom_id')
                                 ->on('sp.semester', '=', 'cs.semester');
                         }
               )
        ->select('cs.classroom_id', 'cs.semester', DB::raw('sum(amount_credit) AS sum_amount_credit'), 'cs.decision_date', 'cs.first_day_of_school', 'cs.collect_began_date')
        ->whereRaw('(lm.deleted_time IS NULL OR lm.deleted_time=0)')
        ->whereRaw('(sp.deleted_time IS NULL OR sp.deleted_time=0)')
        ->groupBy('cs.classroom_id', 'cs.semester', 'cs.decision_date', 'cs.first_day_of_school', 'cs.collect_began_date')
        ->orderBy('cs.classroom_id', 'asc')
        ->orderBy('cs.semester', 'asc')
        ->get();
        return $results;
    }

    public function getStudentsByClass(){

        //History 
        
        // $results = DB::table('students AS s')
        // ->join('student_classrooms AS sc', 'sc.student_id', '=', 's.id')
        // ->join('student_revision_histories as srh', 'srh.student_id', '=', 's.id')
        // ->select('s.student_profile_id', 'sc.classroom_id', 'srh.value', 'srh.began_at', 'srh.ended_at', 'sc.began_at as sc_began_at','sc.ended_at as sc_ended_at')
        // ->where('s.deleted_time', 0)
        // ->where('srh.type', 2)
        // ->orderBy('sc.classroom_id')
        // ->get();

        //Daily Job
        $results = DB::table('students AS s')
        ->join('student_classrooms AS sc', 'sc.student_id', '=', 's.id')
        ->select('s.student_profile_id', 'sc.classroom_id')
        ->where('s.student_status', 8)->where('s.deleted_time', 0)
        ->whereRaw('sc.ended_at IS NULL OR sc.ended_at  >= now()')
        ->orderBy('sc.classroom_id')
        ->get();
        return $results;
    }

    public function getCreditPrices(){
        $results = DB::table('credit_prices AS c')
        ->select('effective_date', 'price')
        ->whereRaw('(c.deleted_time IS NULL OR c.deleted_time=0)')
        ->get();
        return $results;
    }

    public function getIgnoreLearningModule(){
        $results = DB::table('ignore_learning_modules AS ilm')
        ->join('study_plans AS sp', 'ilm.learning_module_id', '=', 'sp.learning_module_id')
        ->join('learning_modules AS lm', 'ilm.learning_module_id', '=', 'lm.id')
        ->join('students AS s', 'ilm.student_id', '=', 's.id')
        ->join('student_classrooms AS sc', 'ilm.student_id', '=', 'sc.student_id')
        ->whereRaw('sp.classroom_id = sc.classroom_id')
        ->select('s.student_profile_id', 'sc.classroom_id', 'ilm.learning_module_id', 'sp.semester', 'lm.amount_credit', 'ilm.created_at')
        ->get();
        return $results;
    }

}