<?php

namespace App\Http\Domain\Student\Repositories\LearningProcess;

use App\Helpers\Interfaces\ThrowIfNotAbleInterface;
use App\Helpers\Traits\ThrowIfNotAble;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquent\LearningProcess;
use App\Eloquent\Student;
use App\Eloquent\StudentClassroom;
use App\Http\Domain\Student\Models\LearningProcess\LearningProcess as LearningProcessModel;
use App\Http\Domain\Student\Requests\LearningProcess\SearchRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Enum\StudentReceivablePurpose;
use App\Eloquent\Crm\Student as StudentCrm;
use App\Eloquent\LearningModule;
use App\Http\Enum\PetitionStatus;

class LearningProcessRepository implements LearningProcessRepositoryInterface, ThrowIfNotAbleInterface
{
    use ThrowIfNotAble;

    /** @var Builder|\Illuminate\Database\Eloquent\Model */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = LearningProcess::query()->getModel();
    }

    /**
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator
    {
        $query = $this->learningProcessRepositoryQuery($request);
        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        // $paginate->getCollection()->transform(function ($learning_process) {
        //     return new LearningProcessModel($learning_process);
        // });
        return $paginate;
    }

     /**
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array
    {
        try {
            $attr = [
                    'learning_modules_id' => LearningModule::query()->where('code', $validator['learning_module_code'])->first()->id,
                    'student_id' => Student::query()->where('account', $validator['account'])->first()->id,
                    'item_type' => $validator['item_type'],
                ];
            $value = [
                    'result_btgk1' => $validator['result_btgk1'],
                    'result_btgk2' => $validator['result_btgk2'],
                    'result_diem_cc' => $validator['result_diem_cc'],
                    'deadline_btgk1' =>  Carbon::parse($validator['deadline_btgk1']),
                    'deadline_btgk2' => Carbon::parse($validator['deadline_btgk2']),
                    'deadline_diem_cc' => Carbon::parse($validator['deadline_diem_cc']),
                ];
            $learning_process = LearningProcess::updateOrCreate($attr, $value);
            return (array)new LearningProcessModel($learning_process);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    public function insert($sql)
    {
        try { 
            return DB::statement($sql);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param SearchRequest $request
     * @return Builder|\Illuminate\Database\Eloquent\Model
     */
    public function learningProcessRepositoryQuery(SearchRequest $request): Builder|\Illuminate\Database\Eloquent\Model
    {
        $now = Carbon::now();
        $query = StudentClassroom::with([
            'student' => function ($q) use ($now) {
                $q->select('id', 'student_profile_id', 'student_code', 'student_status');
                $q->with([
                    'studentProfile' => function ($q) {
                        $q->select('id', 'profile_id', 'profile_code');
                        $q->with(['receivable' => function ($q) {
                            $q->where('purpose', StudentReceivablePurpose::getValueByKeyVi('HOC_PHI'));
                        }]);
                    },
                    'getProfile:firstname,lastname',
                    'petitions',
                    'careHistories'
                ]);
            },
            'studyPlans' => function ($q) use ($now) {
                $q->select('id', 'classroom_id', 'semester', 'study_began_date', 'learning_module_id');
                $q->where('study_began_date', '<=', Carbon::parse($now)->addDays(7));
                $q->where('day_of_the_test', '>=', $now);
                $q->orderBy('semester', 'asc');
                $q->with([
                    'learningModule' => function ($q) {
                        $q->select('id', 'code', 'subject_id');
                        $q->with('subject:id,name');
                    },
                    'learningProcess'
                ]);
            },
            'getClassroom:id,code',
        ])
            ->where(function ($q) use ($now) {
                $q->orWhereNull('ended_at')->orWhereDate('ended_at', '<=', $now);
            })
            ->whereExists(function ($q) {
                $q->select('classrooms.id')->from('classrooms')->join('staffs', 'staffs.id', '=', 'classrooms.staff_id')
                    ->whereRaw('(classrooms.deleted_time is null or classrooms.deleted_time = 0)')
                    ->whereRaw('classrooms.deleted_time is null or staffs.deleted_time = 0')
                    ->whereRaw('classrooms.id = student_classrooms.classroom_id')
                    ->where('staffs.user_id',  auth()->getId());
            })
            ->whereExists(function ($q) use ($now) {
                $q->select('study_plans.id')->from('study_plans')
                    ->whereRaw('study_plans.classroom_id = student_classrooms.classroom_id')
                    ->where('study_began_date', '<=', Carbon::parse($now)->addDays(7))
                    ->where('day_of_the_test', '>=', $now);
            });
        $query->select('id', 'student_id', 'classroom_id');
        $query->orderBy('id', 'desc');

        if ($request->classroom_id) {
            $query->whereIn('classroom_id', explode(',', $request->classroom_id));
        }
        if ($request->profile_code) {
            $query->whereHas('student.studentProfile', function ($q) use ($request) {
                $q->whereILike('profile_code', $request->profile_code);
            });
        }
        if ($request->student_code) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereILike('student_code', $request->student_code);
            });
        }
        if ($request->fullname) {
            $query->whereHas('student.getProfile', function ($q) use ($request) {
                $q->where(DB::raw("CONCAT(firstname, ' ', lastname)"), "ILIKE", "%$request->fullname%");
            });
        }

        if ($request->btgk1_status == 'completed') {
            $query->whereExists(function ($q) use ($now){
                $q->select('study_plans.id')->from('study_plans')
                    ->join('learning_processes', 'learning_processes.learning_modules_id', '=', 'study_plans.learning_module_id')
                    ->where('study_began_date', '<=', Carbon::parse($now)->addDays(7))
                    ->where('day_of_the_test', '>=', $now)
                    ->whereRaw('learning_processes.student_id = student_classrooms.student_id')
                    ->whereRaw('learning_processes.result_btgk1 is not null');
            });
        }
        if ($request->btgk1_status == 'notcompleted') {
            $query->whereNotExists(function ($q) use ($now){
                $q->select('study_plans.id')->from('study_plans')
                    ->join('learning_processes', 'learning_processes.learning_modules_id', '=', 'study_plans.learning_module_id')
                    ->where('study_began_date', '<=', Carbon::parse($now)->addDays(7))
                    ->where('day_of_the_test', '>=', $now)
                    ->whereRaw('learning_processes.student_id = student_classrooms.student_id')
                    ->whereRaw('learning_processes.result_btgk1 is not null');
            });
        }
        if ($request->btgk2_status == 'completed') {
            $query->whereExists(function ($q) use ($now) {
                $q->select('study_plans.id')->from('study_plans')
                    ->join('learning_processes', 'learning_processes.learning_modules_id', '=', 'study_plans.learning_module_id')
                    ->where('study_began_date', '<=', Carbon::parse($now)->addDays(7))
                    ->where('day_of_the_test', '>=', $now)
                    ->whereRaw('learning_processes.student_id = student_classrooms.student_id')
                    ->whereRaw('learning_processes.result_btgk2 is not null');
            });
        }
        if ($request->btgk2_status == 'notcompleted') {
            $query->whereNotExists(function ($q) use ($now) {
                $q->select('study_plans.id')->from('study_plans')
                    ->join('learning_processes', 'learning_processes.learning_modules_id', '=', 'study_plans.learning_module_id')
                    ->where('study_began_date', '<=', Carbon::parse($now)->addDays(7))
                    ->where('day_of_the_test', '>=', $now)
                    ->whereRaw('learning_processes.student_id = student_classrooms.student_id')
                    ->whereRaw('learning_processes.result_btgk2 is not null');
            });
        }
        if ($request->diem_cc_status == 'completed') {
            $query->whereExists(function ($q) use ($now){
                $q->select('study_plans.id')->from('study_plans')
                    ->join('learning_processes', 'learning_processes.learning_modules_id', '=', 'study_plans.learning_module_id')
                    ->where('study_began_date', '<=', Carbon::parse($now)->addDays(7))
                    ->where('day_of_the_test', '>=', $now)
                    ->whereRaw('learning_processes.student_id = student_classrooms.student_id')
                    ->whereRaw('learning_processes.result_diem_cc is not null');
            });
        }
        if ($request->diem_cc_status == 'notcompleted') {
            $query->whereNotExists(function ($q) use ($now) {
                $q->select('study_plans.id')->from('study_plans')
                    ->join('learning_processes', 'learning_processes.learning_modules_id', '=', 'study_plans.learning_module_id')
                    ->where('study_began_date', '<=', Carbon::parse($now)->addDays(7))
                    ->where('day_of_the_test', '>=', $now)
                    ->whereRaw('learning_processes.student_id = student_classrooms.student_id')
                    ->whereRaw('learning_processes.result_diem_cc is not null');
            });
        }

        if ($request->petition_status == 'noprocess') {
            $query->whereExists(function ($q) {
                $q->select('petitions.id')->from('petitions')
                    ->whereRaw('petitions.student_id = student_classrooms.student_id')
                    ->where('status', PetitionStatus::LEARNING_MANAGEMENT_SEND);
            });
        }
        if ($request->petition_status == 'processing') {
            $query->whereExists(function ($q) {
                $q->select('petitions.id')->from('petitions')
                    ->whereRaw('petitions.student_id = student_classrooms.student_id')
                    ->whereIn('status', [PetitionStatus::ACADEMIC_AFFAIR_ACCEPT, PetitionStatus::ACADEMIC_AFFAIR_SEND]);
            });
        }
        if ($request->petition_status == 'processed') {
            $query->whereExists(function ($q) {
                $q->select('petitions.id')->from('petitions')
                    ->whereRaw('petitions.student_id = student_classrooms.student_id')
                    ->whereNotIn('status', [PetitionStatus::LEARNING_MANAGEMENT_SEND, PetitionStatus::ACADEMIC_AFFAIR_ACCEPT, PetitionStatus::ACADEMIC_AFFAIR_SEND]);
            });
        }

        return $query;
    }

    /**
     * getListItems
     *
     * @return array
     */
    public function getListItems(): array
    {
        return $this->model->get()->toArray();
    }

    public function getAmountsReceived(string $profile_code)
    {
        $result = StudentCrm::query()->with([
            'amountsReceived' => function ($q) {
                $q->softDelete();
            },
        ])->where('ma_ho_so', $profile_code)->first();
        return $result;
    }
}
