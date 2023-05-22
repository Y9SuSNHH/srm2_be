<?php

namespace App\Http\Domain\Finance\Repositories\Finance;

use App\Eloquent\Classroom;
use App\Http\Domain\Finance\Requests\Finance\SearchRequest;
use App\Http\Domain\Finance\Requests\Finance\FilterRequest;
use App\Http\Domain\Finance\Requests\Finance\StudentClassRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Eloquent\Transaction;
use App\Eloquent\FinancialCredit;
use App\Eloquent\StudyPlan;
use App\Eloquent\CreditPrice;
use App\Eloquent\Period;
use App\Eloquent\Crm\Student as StudentCrm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Http\Domain\Finance\Models\Finance\FinanceByStudent as FinanceByStudent;
use App\Http\Domain\Finance\Models\Finance\StudentByClass as StudentByClass;
use App\Http\Domain\Finance\Requests\Finance\EditRequest;
use App\Http\Domain\Finance\Requests\Finance\TuitionRequest;
use App\Http\Enum\RoleAuthority;
use App\Http\Enum\StudentReceivablePurpose;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use stdClass;

/**
 * Class FinanceRepository
 * @package App\Http\Domain\Receivable\Repositories\Receivable
 */
class FinanceRepository implements FinanceRepositoryInterface
{
    /**
     * @var Builder|Model
     */
    private Builder|Model $transaction_eloquent;

    private Builder|Model $finance_credit_eloquent;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->transaction_eloquent = Transaction::query()->getModel();
        $this->finance_credit_eloquent = FinancialCredit::query()->getModel();
    }

    public function query(int $staff = null)
    {
        // WHERE (semester = 1) 
        // WHERE (semester = 1 AND collect_began_date >= NOW())

        return DB::table('study_plans as sp')
            ->join(DB::raw('(SELECT classroom_id, semester, collect_began_date, decision_date FROM periods) as p'), function ($join) {
                $join->on('sp.classroom_id', '=', 'p.classroom_id')
                    ->on('sp.semester', '=', 'p.semester');
            })
            ->join('classrooms as c', function ($join) {
                $join->on('c.id', '=', 'sp.classroom_id')
                    ->where('c.deleted_time', '=', 0);
            })
            ->join('majors as m', function ($join) {
                $join->on('m.id', '=', 'c.major_id')
                    ->where('m.deleted_time', '=', 0);
            })
            ->join('staffs as s', function ($join) {
                $join->on('s.id', '=', 'c.staff_id')
                    ->where('s.deleted_time', '=', 0)
                    ->where('s.status', '=', 'working');
            })
            ->join(DB::raw('(SELECT sc.classroom_id, COUNT(sc.student_id) AS number_student FROM student_classrooms as sc
                        INNER JOIN students as std ON sc.student_id = std.ID 
                        WHERE std.deleted_time = 0 AND std.student_status = 8
                        GROUP BY sc.classroom_id) as stds'), 'stds.classroom_id', '=', 'sp.classroom_id')
            ->where('sp.deleted_time', '=', 0)
            ->where('c.school_id', school()->getId())
            ->when($staff, function ($query) use ($staff) {
                $query->where('s.id', $staff)->where('s.deleted_time', 0);
            });
    }

    public function getByClass(SearchRequest $request): LengthAwarePaginator
    {
        $per_page = $request->perPage();
        $request = $request->validated();

        $query = $this->query()->orderBy('p.collect_began_date', 'desc')
            ->select('sp.classroom_id', 'c.code as class', 's.fullname as qlht', 'sp.semester', 'p.collect_began_date as day_begin', 'stds.number_student', 'm.name as major')
            ->groupBy('sp.classroom_id', 'c.code', 's.fullname', 'sp.semester', 'p.collect_began_date', 'stds.number_student', 'm.name');

        if (!empty($request['major'])) {
            $query->where('m.id', $request['major']);
        }

        if (!empty($request['semester'])) {
            $query->where('sp.semester', $request['semester']);
        }

        if (!empty($request['classId'])) {
            $query->where('c.id', $request['classId']);
        }

        if (!empty($request['staff'])) {
            $query->where('s.id', $request['staff']);
        }

        if (!empty($request['g_date'])) {
            $query->where('p.decision_date', $request['g_date']);
        }

        if (!empty($request['receivable_date'])) {
            $query->where('p.collect_began_date', $request['receivable_date']);
        }

        $data = $query->paginate($per_page);
        $data->getCollection()->transform(function ($item) {
            $item->day_begin = date('d/m/Y', strtotime($item->day_begin));
            return $item;
        });

        return $data;
    }

    public function getFilter(FilterRequest $request)
    {
        $staff_id = $request->staff ?? null;

        $semester = $this->query($staff_id)->select(DB::raw('distinct sp.semester'))->pluck('sp.semester');
        $class = $this->query($staff_id)->select(DB::raw('distinct c.code AS class'), 'c.id')->orderBy('c.id')->pluck('class', 'c.id');
        $staff = $this->query()->select(DB::raw('distinct s.fullname AS qlht'), 's.id')->orderBy('s.id')->pluck('qlht', 's.id');
        $major = $this->query($staff_id)->select(DB::raw('distinct m.name AS major'), 'm.id')->orderBy('m.id')->pluck('major', 'm.id');
        $collect_began_date = $this->query($staff_id)->select(DB::raw("DISTINCT collect_began_date"))->orderBy('collect_began_date', 'desc')->pluck('collect_began_date');
        $decision_date = $this->query($staff_id)->select(DB::raw("DISTINCT decision_date"))->orderBy('decision_date', 'desc')->pluck('decision_date');
        $return = [
            'semester' => $semester,
            'class' => $class,
            'staff' => $staff,
            'major' => $major,
            'collect_began_date' => $collect_began_date,
            'decision_date' => $decision_date,
        ];
        return $return;
    }

    public function getByStudent(SearchRequest $request): LengthAwarePaginator
    {
        $per_page = $request->perPage();
        $request  = $request->validated();
        $query = $this->finance_credit_eloquent->clone();
        $query->with(
            [
                'studentProfile' => function ($q) {
                    $q->select('student_profiles.id', 'student_profiles.profile_id', 'student_profiles.profile_code');
                    $q->with('getProfile:id,firstname,lastname,birthday');
                },
                'student' => function ($q) {
                    $q->select('students.id', 'students.student_profile_id', 'students.student_code');
                    $q->with([
                        'classroom' => function ($q) {
                            $q->select('classrooms.code', 'classrooms.staff_id', 'classrooms.area_id', 'classrooms.id');
                            $q->with([
                                'staff:id,fullname',
                                'area:id,code',
                                'studyPlans' => function ($q) {
                                    $q->select('study_plans.id', 'study_plans.learning_module_id', 'study_plans.subject_id', 'study_plans.classroom_id', 'study_plans.semester');
                                    $q->with([
                                        'learningModule' => function ($q) {
                                            $q->select('learning_modules.id', 'learning_modules.subject_id', 'learning_modules.amount_credit');
                                            $q->with('subject:id,name');
                                        },
                                    ]);
                                },
                                'period' => function ($q) {
                                    $q->whereIn('id', function ($sub) {
                                        $sub->select('p.id')->from('financial_credits as fc')
                                            ->join('students as s', 'fc.student_profile_id', '=', 's.student_profile_id')
                                            ->join('student_classrooms as sc', 'sc.student_id', '=', 's.id')
                                            ->join('classrooms as c', 'sc.classroom_id', '=', 'c.id')
                                            ->join('periods as p', function ($join) {
                                                $join->on('p.classroom_id', '=', 'c.id')
                                                    ->whereRaw('p.semester = CAST(fc.no as SMALLINT)');
                                            })
                                            ->whereRaw('(fc.deleted_time IS NULL OR fc.deleted_time=0)')
                                            ->whereRaw('(s.deleted_time IS NULL OR s.deleted_time=0)')
                                            ->whereRaw('(c.deleted_time IS NULL OR c.deleted_time=0)')
                                            ->groupBy('p.id');
                                    });
                                }
                            ]);
                        },
                        'ignoreLearningModules:id,student_id,learning_module_id'
                    ]);
                }
            ]
        );

        $query->whereHas('student', function ($q) {
            $q->where('school_id', school()->getId());
        });

        if (isset($request['semester'])) {
            $query->where("no",  $request['semester']);
        }

        if (isset($request['purpose']) && $request['purpose'] !== '') {
            $query->where('purpose', $request['purpose']);
        }

        if (isset($request['studentStatus']) && $request['studentStatus'] !== '') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('student_status', $request['studentStatus']);
            });
        }

        if (!empty($request['g_date'])) {

            $period = Period::where('decision_date', $request['g_date'])->get(['classroom_id', 'semester'])->toArray();

            $query->where(function ($q) use ($period) {
                foreach ($period as $key => $value) {
                    if ($key == 0) {
                        $q->where('no', $value['semester'])
                            ->whereHas('student.classroom', function ($sub) use ($value) {
                                $sub->where('classrooms.id', $value['classroom_id']);
                            });
                    } else {
                        $q->orWhere(function ($q) use ($value) {
                            $q->where('no', $value['semester'])
                                ->whereHas('student.classroom', function ($sub) use ($value) {
                                    $sub->where('classrooms.id', $value['classroom_id']);
                                });
                        });
                    }
                }
            });
        }

        if (!empty($request['receivable_date'])) {

            $period = Period::where('collect_began_date', $request['receivable_date'])->get(['classroom_id', 'semester'])->toArray();

            $query->where(function ($q) use ($period) {
                foreach ($period as $key => $value) {
                    if ($key == 0) {
                        $q->where('no', $value['semester'])
                            ->whereHas('student.classroom', function ($sub) use ($value) {
                                $sub->where('classrooms.id', $value['classroom_id']);
                            });
                    } else {
                        $q->orWhere(function ($q) use ($value) {
                            $q->where('no', $value['semester'])
                                ->whereHas('student.classroom', function ($sub) use ($value) {
                                    $sub->where('classrooms.id', $value['classroom_id']);
                                });
                        });
                    }
                }
            });
        }

        if (!empty($request['staff'])) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('classroom', function ($sub) use ($request) {
                    $sub->where('staff_id', $request['staff']);
                });
            });
        }

        if (!empty($request['classId'])) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('classroom', function ($sub) use ($request) {
                    $sub->where('classrooms.id', $request['classId']);
                });
            });
        }

        if (!empty($request['fullname'])) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->whereHas('getProfile', function ($sub) use ($request) {
                    $sub->where(DB::raw("lower(CONCAT(firstname, ' ', lastname))"), "ilike", $request['fullname']);
                });
            });
        }

        if (!empty($request['student_code'])) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where("profile_code", 'ilike', $request['student_code']);
            });

            $query->orWhereHas('student', function ($q) use ($request) {
                $q->where("student_code", 'like', $request['student_code']);
            });
        }

        if (RoleAuthority::LEARNING_MANAGEMENT()->check()) {
            if (!RoleAuthority::PM()->check()) {
                $query->whereHas('student.classrooms', function ($q) {
                    $q->where("staff_id", auth()->user()->getStaffId());
                });
            }
        }

        $query->orderBy('id', 'desc');
        $now = Carbon::now();
        $price = CreditPrice::where('effective_date', '=', function ($subquery) use ($now) {
            $subquery->select(DB::raw('MAX(effective_date)'))
                ->from('credit_prices')
                ->where('effective_date', '<=', $now);
        })->pluck('price')->first();

        $data = $query->makePaginate($per_page);
        $data->getCollection()->transform(function ($finance) use ($price) {
            return new FinanceByStudent($finance, $price);
        });
        return $data;
    }

    public function studentClass(StudentClassRequest $request)
    {
        $request = $request->validated();
        $now = Carbon::now();
        $data = Classroom::where('id', $request['classId'])
            ->with([
                'period' => function ($q) {
                    $q->select('periods.classroom_id', 'periods.semester');
                },
                'staff',
                'area',
                'students' => function ($q) use ($request, $now) {
                    $q->select('students.id', 'students.student_code', 'students.student_profile_id');
                    $q->distinct();
                    $q->with([
                        'getProfile' => function ($query) {
                            $query->select('profiles.id', 'firstname', 'lastname');
                        },
                        'studentProfile' => function ($query) {
                            $query->select('student_profiles.id', 'profile_code');
                        },
                    ]);

                    $q->where('student_status', 8);

                    $q->whereIn('students.id', function ($sub) use ($request, $now) {
                        $sub->select('student_id')->from('student_classrooms')->where('classroom_id', $request['classId'])->where(function ($query) use ($now) {
                            $query->whereNull('ended_at')->orWhere('ended_at', '>=', $now);
                        });
                    });

                    if (!empty($request['searchStudent'])) {
                        $q->whereHas('getProfile', function ($query) use ($request) {
                            $query->where(DB::raw("lower(CONCAT(firstname, ' ', lastname))"), 'ilike', $request['searchStudent']);
                        })
                            ->orWhere('students.student_code', 'like', $request['searchStudent'])
                            ->orWhereHas('studentProfile', function ($query) use ($request) {
                                $query->where('student_profiles.profile_code', 'like', $request['searchStudent']);
                            });
                    }

                    $q->whereHas('studentClassrooms', function ($query) use ($request, $now) {
                        $query->whereNull('ended_at')->orWhere('ended_at', '>=', $now)->where('classroom_id', $request['classId']);
                    });
                },
            ])->get();
        $data->transform(function ($student) {
            return new StudentByClass($student);
        });
        return $data;
    }

    public function filterStudent(int $purpose = null)
    {
        $query = DB::table('financial_credits as fc')
            ->join('students as s', 'fc.student_profile_id', '=', 's.student_profile_id')
            ->join('student_classrooms as sc', 'sc.student_id', '=', 's.id')
            ->join('classrooms as c', 'sc.classroom_id', '=', 'c.id')
            ->join('enrollment_waves as ew', 'c.enrollment_wave_id', '=', 'ew.id')
            ->join('staffs as stf', 'c.staff_id', '=', 'stf.id')
            ->leftJoin('periods as p', function ($join) {
                $join->on('p.classroom_id', '=', 'c.id')
                    ->whereRaw('p.semester = CAST(fc.no as SMALLINT)');
            })
            ->where('c.school_id', school()->getId())
            ->whereRaw('(fc.deleted_time IS NULL OR fc.deleted_time=0)')
            ->whereRaw('(s.deleted_time IS NULL OR s.deleted_time=0)')
            ->whereRaw('(c.deleted_time IS NULL OR c.deleted_time=0)')
            ->whereRaw('(stf.deleted_time IS NULL OR stf.deleted_time=0)');

        if (RoleAuthority::LEARNING_MANAGEMENT()->check()) {
            if (!RoleAuthority::PM()->check()) {
                $query->where("c.staff_id", auth()->user()->getStaffId());
            }
        }

        $semesterArr = $query->clone()
            ->select('fc.no')
            ->groupBy('fc.no')
            ->pluck('fc.no');

        if ($purpose) {
            $semesterArr->where('purpose', StudentReceivablePurpose::TUITION_FEE);
        }

        $class = $query->clone()
            ->select('c.id', 'c.code')
            ->groupBy('c.id', 'c.code')
            ->pluck('c.code', 'c.id');

        $staff = $query->clone()
            ->select('stf.id', 'stf.fullname')
            ->groupBy('stf.id', 'stf.fullname')
            ->pluck('stf.fullname', 'stf.id');

        $decisionArr = $query->clone()
            ->select('p.decision_date')
            ->orderBy('p.decision_date', 'desc')
            ->groupBy('p.decision_date')
            ->pluck('p.decision_date');

        $startArr = $query->clone()
            ->select('ew.first_day_of_school')
            ->orderBy('ew.first_day_of_school', 'desc')
            ->groupBy('ew.first_day_of_school')
            ->pluck('ew.first_day_of_school');

        $collectArr = $query->clone()
            ->select('p.collect_began_date')
            ->orderBy('p.collect_began_date', 'desc')
            ->groupBy('p.collect_began_date')
            ->pluck('p.collect_began_date');

        $semester = new stdClass();

        foreach ($semesterArr as $smt) {
            $semester->$smt = $smt;
        }

        $decision = new stdClass();

        foreach ($decisionArr as $date) {
            if ($date) {
                $decision->$date = $date;
            }
        }

        $start = new stdClass();

        foreach ($startArr as $date) {
            if ($start) {
                $start->$date = $date;
            }
        }

        $collect = new stdClass();

        foreach ($collectArr as $date) {
            if ($date) {
                $collect->$date = $date;
            }
        }

        $return = [
            'class' => $class,
            'staff' => $staff,
            'semester' => $semester,
            'decision' => $decision,
            'startDate' => $start,
            'collectDate' => $collect,
            'purpose' => $purpose
        ];
        return $return;
    }

    public function getAllByClass(SearchRequest $request)
    {
        $request = $request->validated();

        $query = $this->query()->orderBy('p.collect_began_date')
            ->select('sp.classroom_id', 'c.code as class', 's.fullname as qlht', 'sp.semester', 'p.collect_began_date as day_begin', 'stds.number_student', 'm.name as major')
            ->groupBy('sp.classroom_id', 'c.code', 's.fullname', 'sp.semester', 'p.collect_began_date', 'stds.number_student', 'm.name');

        if (!empty($request['major'])) {
            $query->where('m.id', $request['major']);
        }

        if (!empty($request['semester'])) {
            $query->where('sp.semester', $request['semester']);
        }

        if (!empty($request['classId'])) {
            $query->where('c.id', $request['classId']);
        }

        if (!empty($request['staff'])) {
            $query->where('s.id', $request['staff']);
        }

        if (!empty($request['g_date'])) {
            $query->where('p.decision_date', $request['g_date']);
        }

        if (!empty($request['receivable_date'])) {
            $query->where('p.collect_began_date', $request['receivable_date']);
        }

        if (!empty($request['class_semester'])) {
            $decode = urldecode($request['class_semester']);
            $array = json_decode($decode, true);
            foreach ($array as $key => $value) {
                $classroomId = $value['classroom_id'];
                $semester = $value['semester'];
                if ($key === 0) {
                    $query->where('c.id', $classroomId)->where('sp.semester', $semester);
                } else {
                    $query->orWhere('c.id', $classroomId)->where('sp.semester', $semester);
                }
            }
        }

        $data = $query->get();
        $data->transform(function ($item) {
            $item->day_begin = date('d/m/Y', strtotime($item->day_begin));
            return $item;
        });

        return $data;
    }

    public function getAllByStudent(SearchRequest $request)
    {
        $request = $request->validated();
        $query = FinancialCredit::with(
            [
                'studentProfile' => function ($q) {
                    $q->select('student_profiles.id', 'student_profiles.profile_id', 'student_profiles.profile_code');
                    $q->with('getProfile:id,firstname,lastname,birthday');
                },
                'student' => function ($q) {
                    $q->select('students.id', 'students.student_profile_id', 'students.student_code');
                    $q->with(['classroom' => function ($q) {
                        $q->select('classrooms.code', 'classrooms.staff_id', 'classrooms.area_id', 'classrooms.id');
                        $q->with([
                            'staff:id,fullname',
                            'area:id,code'
                        ]);
                    }]);
                }

            ]
        );

        $query->whereHas('student', function ($q) {
            $q->where('school_id', school()->getId());
        });

        if (isset($request['semester'])) {
            $query->where("no",  $request['semester']);
        }

        if (isset($request['purpose']) && $request['purpose'] !== '') {
            $query->where('purpose', $request['purpose']);
        }

        if (isset($request['studentStatus']) && $request['studentStatus'] !== '') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('student_status', $request['studentStatus']);
            });
        }

        if (!empty($request['g_date'])) {
            $period = Period::where('decision_date', $request['g_date'])->get(['classroom_id', 'semester'])->toArray();

            $query->where(function ($q) use ($period) {
                foreach ($period as $key => $value) {
                    if ($key == 0) {
                        $q->where('no', $value['semester'])
                            ->whereHas('student.classroom', function ($sub) use ($value) {
                                $sub->where('classrooms.id', $value['classroom_id']);
                            });
                    } else {
                        $q->orWhere(function ($q) use ($value) {
                            $q->where('no', $value['semester'])
                                ->whereHas('student.classroom', function ($sub) use ($value) {
                                    $sub->where('classrooms.id', $value['classroom_id']);
                                });
                        });
                    }
                }
            });
        }

        if (!empty($request['receivable_date'])) {
            $period = Period::where('collect_began_date', $request['receivable_date'])->get(['classroom_id', 'semester'])->toArray();

            $query->where(function ($q) use ($period) {
                foreach ($period as $key => $value) {
                    if ($key == 0) {
                        $q->where('no', $value['semester'])
                            ->whereHas('student.classroom', function ($sub) use ($value) {
                                $sub->where('classrooms.id', $value['classroom_id']);
                            });
                    } else {
                        $q->orWhere(function ($q) use ($value) {
                            $q->where('no', $value['semester'])
                                ->whereHas('student.classroom', function ($sub) use ($value) {
                                    $sub->where('classrooms.id', $value['classroom_id']);
                                });
                        });
                    }
                }
            });
        }

        if (!empty($request['staff'])) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('classroom', function ($sub) use ($request) {
                    $sub->where('staff_id', $request['staff']);
                });
            });
        }

        if (!empty($request['classId'])) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('classroom', function ($sub) use ($request) {
                    $sub->where('classrooms.id', $request['classId']);
                });
            });
        }

        if (!empty($request['fullname'])) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->whereHas('getProfile', function ($sub) use ($request) {
                    $sub->where(DB::raw("lower(CONCAT(firstname, ' ', lastname))"), "ilike", $request['fullname']);
                });
            });
        }

        if (!empty($request['student_code'])) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where("profile_code", 'ilike', $request['student_code']);
            });

            $query->orWhereHas('student', function ($q) use ($request) {
                $q->where("student_code", 'like', $request['student_code']);
            });
        }

        if (RoleAuthority::LEARNING_MANAGEMENT()->check()) {
            if (!RoleAuthority::PM()->check()) {
                $query->whereHas('student.classrooms', function ($q) {
                    $q->where("staff_id", auth()->user()->getStaffId());
                });
            }
        }

        $query->orderBy('id', 'desc');

        if (!empty($request['transaction_ids'])) {
            $ids = explode(",", $request['transaction_ids']);
            $query->whereIn('id', $ids);
        }

        $data = $query->get();
        return $data;
    }

    public function semesterClass()
    {
        $result = Classroom::with(['studyPlans' => function ($q) {
            $q->select('classroom_id', 'semester')->groupBy('classroom_id', 'semester')->orderBy('semester');
        }])
            ->whereHas('staff', function ($query) {
                $query->where('user_id', auth()->getId());
            })->whereHas('students')
            ->select('id', 'code', 'staff_id')->get();
        return $result;
    }

    public function receiveSemester(SearchRequest $request)
    {
        $request = $request->validated();
        $now = Carbon::now();
        $return = Period::where('classroom_id', $request['classId'])->where('collect_ended_date', '>=', $now)->select('semester', 'decision_date')->get();
        return $return;
    }

    public function tuition(TuitionRequest $request): LengthAwarePaginator
    {
        $per_page = $request->perPage();
        $request  = $request->validated();
        $query = $this->finance_credit_eloquent->clone();
        $query->with(
            [
                'studentProfile' => function ($q) {
                    $q->select('student_profiles.id', 'student_profiles.profile_id', 'student_profiles.profile_code');
                    $q->with('getProfile:id,firstname,lastname,birthday');
                },
                'student' => function ($q) {
                    $q->select('students.id', 'students.student_profile_id', 'students.student_code');
                    $q->with(['classroom' => function ($q) {
                        $q->select('classrooms.code', 'classrooms.staff_id', 'classrooms.area_id', 'classrooms.id', 'classrooms.enrollment_wave_id');
                        $q->with([
                            'staff:id,fullname',
                            'area:id,code',
                            'studyPlans' => function ($q) {
                                $q->select('study_plans.id', 'study_plans.learning_module_id', 'study_plans.subject_id', 'study_plans.classroom_id', 'study_plans.semester');
                                $q->with([
                                    'learningModule' => function ($q) {
                                        $q->select('learning_modules.id', 'learning_modules.subject_id', 'learning_modules.amount_credit');
                                        $q->with('subject:id,name');
                                    },
                                ]);
                            },
                            'period' => function ($q) {
                                $q->whereIn('id', function ($sub) {
                                    $sub->select('p.id')->from('financial_credits as fc')
                                        ->join('students as s', 'fc.student_profile_id', '=', 's.student_profile_id')
                                        ->join('student_classrooms as sc', 'sc.student_id', '=', 's.id')
                                        ->join('classrooms as c', 'sc.classroom_id', '=', 'c.id')
                                        ->join('periods as p', function ($join) {
                                            $join->on('p.classroom_id', '=', 'c.id')
                                                ->whereRaw('p.semester = CAST(fc.no as SMALLINT)');
                                        })
                                        ->whereRaw('(fc.deleted_time IS NULL OR fc.deleted_time=0)')
                                        ->whereRaw('(s.deleted_time IS NULL OR s.deleted_time=0)')
                                        ->whereRaw('(c.deleted_time IS NULL OR c.deleted_time=0)')
                                        ->groupBy('p.id');
                                });
                            },
                            'enrollmentWave'
                        ]);
                    }]);
                }
            ]
        );

        $query->whereHas('student', function ($q) {
            $q->where('school_id', school()->getId());
        });

        if (isset($request['semester'])) {
            $query->where("no",  $request['semester']);
        }

        if (isset($request['studentStatus']) && $request['studentStatus'] !== '') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('student_status', $request['studentStatus']);
            });
        }

        if (!empty($request['startDate'])) {
            $query->whereHas('student.classroom.enrollmentWave', function ($q) use ($request) {
                $q->where('first_day_of_school', $request['startDate']);
            });
        }

        if (!empty($request['receivable_date'])) {
            $period = Period::where('collect_began_date', $request['receivable_date'])->get(['classroom_id', 'semester'])->toArray();

            $query->where(function ($q) use ($period) {
                foreach ($period as $key => $value) {
                    if ($key == 0) {
                        $q->where('no', $value['semester'])
                            ->whereHas('student.classroom', function ($sub) use ($value) {
                                $sub->where('classrooms.id', $value['classroom_id']);
                            });
                    } else {
                        $q->orWhere(function ($q) use ($value) {
                            $q->where('no', $value['semester'])
                                ->whereHas('student.classroom', function ($sub) use ($value) {
                                    $sub->where('classrooms.id', $value['classroom_id']);
                                });
                        });
                    }
                }
            });
        }

        if (!empty($request['staff'])) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('classroom', function ($sub) use ($request) {
                    $sub->where('staff_id', $request['staff']);
                });
            });
        }

        if (!empty($request['classId'])) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('classroom', function ($sub) use ($request) {
                    $sub->where('classrooms.id', $request['classId']);
                });
            });
        }

        if (!empty($request['fullname'])) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->whereHas('getProfile', function ($sub) use ($request) {
                    $sub->where(DB::raw("lower(CONCAT(firstname, ' ', lastname))"), "ilike", $request['fullname']);
                });
            });
        }

        if (!empty($request['student_code'])) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where("profile_code", 'ilike', $request['student_code']);
            });

            $query->orWhereHas('student', function ($q) use ($request) {
                $q->where("student_code", 'like', $request['student_code']);
            });
        }

        $query->where('purpose', StudentReceivablePurpose::TUITION_FEE);

        if (RoleAuthority::LEARNING_MANAGEMENT()->check()) {
            if (!RoleAuthority::PM()->check()) {
                $query->whereHas('student.classrooms', function ($q) {
                    $q->where("staff_id", auth()->user()->getStaffId());
                });
            }
        }

        $query->orderBy('id', 'desc');
        $now = Carbon::now();
        $price = CreditPrice::where('effective_date', '=', function ($subquery) use ($now) {
            $subquery->select(DB::raw('MAX(effective_date)'))
                ->from('credit_prices')
                ->where('effective_date', '<=', $now);
        })->pluck('price')->first();

        $data = $query->makePaginate($per_page);
        $data->getCollection()->transform(function ($finance) use ($price) {
            $query = StudentCrm::clone()->with([
                'amountsReceived' => function ($q) use ($finance) {
                    $q->select('id_sinh_vien', DB::raw('CAST(thuc_nop AS UNSIGNED) as thuc_thu'), 'so_chung_tu_bien_lai',  DB::raw("DATE_FORMAT(ngay_bien_lai, '%d/%m/%Y') as ngay_xuat_bien_lai"))
                        ->where('dot_hoc_so', $finance->no)
                        ->where('muc_dich_thu', StudentReceivablePurpose::getValueByKey($finance->purpose))
                        ->softDelete();
                },
            ]);
            $studentCRM = $query->select('id')->where('ma_ho_so', $finance->studentProfile->profile_code)->first();

            $query = StudentCrm::clone()->with([
                'amountsReceived' => function ($q) use ($finance) {
                    $q->select('id_sinh_vien', 'muc_dich_thu', DB::raw('SUM(thuc_nop) as total_thuc_nop'))
                        ->where('dot_hoc_so', $finance->no)
                        ->where('muc_dich_thu', StudentReceivablePurpose::getValueByKey($finance->purpose))
                        ->groupBy('muc_dich_thu')
                        ->softDelete();
                },
            ]);
            $totalReceived = $query->select('id')->where('ma_ho_so', $finance->studentProfile->profile_code)->first();

            $finance->amountsReceived = $studentCRM->amountsReceived;
            $finance->totalReceived = $totalReceived->amountsReceived;

            return new FinanceByStudent($finance, $price);
        });
        return $data;
    }

    public function getTuition(TuitionRequest $request)
    {
        $request = $request->validated();
        $query = FinancialCredit::with(
            [
                'studentProfile' => function ($q) {
                    $q->select('student_profiles.id', 'student_profiles.profile_id', 'student_profiles.profile_code');
                    $q->with('getProfile:id,firstname,lastname,birthday');
                },
                'student' => function ($q) {
                    $q->select('students.id', 'students.student_profile_id', 'students.student_code');
                    $q->with(['classroom' => function ($q) {
                        $q->select('classrooms.code', 'classrooms.staff_id', 'classrooms.area_id', 'classrooms.id');
                        $q->with([
                            'staff:id,fullname',
                            'area:id,code'
                        ]);
                    }]);
                }

            ]
        );
        $query->where('purpose', StudentReceivablePurpose::TUITION_FEE);

        if (RoleAuthority::LEARNING_MANAGEMENT()->check()) {
            if (!RoleAuthority::PM()->check()) {
                $query->whereHas('student.classrooms', function ($q) {
                    $q->where("staff_id", auth()->user()->getStaffId());
                });
            }
        }
        
        $query->orderBy('id', 'desc');

        $query->whereHas('student', function ($q) {
            $q->where('school_id', school()->getId());
        });

        if (!empty($request['transaction_ids'])) {
            $ids = explode(",", $request['transaction_ids']);
            $query->whereIn('id', $ids);
        }

        if (isset($request['semester'])) {
            $query->where("no",  $request['semester']);
        }

        if (isset($request['studentStatus']) && $request['studentStatus'] !== '') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('student_status', $request['studentStatus']);
            });
        }

        if (!empty($request['startDate'])) {
            $query->whereHas('student.classroom.enrollmentWave', function ($q) use ($request) {
                $q->where('first_day_of_school', $request['startDate']);
            });
        }

        if (!empty($request['receivable_date'])) {
            $period = Period::where('collect_began_date', $request['receivable_date'])->get(['classroom_id', 'semester'])->toArray();

            $query->where(function ($q) use ($period) {
                foreach ($period as $key => $value) {
                    if ($key == 0) {
                        $q->where('no', $value['semester'])
                            ->whereHas('student.classroom', function ($sub) use ($value) {
                                $sub->where('classrooms.id', $value['classroom_id']);
                            });
                    } else {
                        $q->orWhere(function ($q) use ($value) {
                            $q->where('no', $value['semester'])
                                ->whereHas('student.classroom', function ($sub) use ($value) {
                                    $sub->where('classrooms.id', $value['classroom_id']);
                                });
                        });
                    }
                }
            });
        }

        if (!empty($request['staff'])) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('classroom', function ($sub) use ($request) {
                    $sub->where('staff_id', $request['staff']);
                });
            });
        }

        if (!empty($request['classId'])) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('classroom', function ($sub) use ($request) {
                    $sub->where('classrooms.id', $request['classId']);
                });
            });
        }

        if (!empty($request['fullname'])) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->whereHas('getProfile', function ($sub) use ($request) {
                    $sub->where(DB::raw("lower(CONCAT(firstname, ' ', lastname))"), "ilike", $request['fullname']);
                });
            });
        }

        if (!empty($request['student_code'])) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where("profile_code", 'ilike', $request['student_code']);
            });

            $query->orWhereHas('student', function ($q) use ($request) {
                $q->where("student_code", 'like', $request['student_code']);
            });
        }

        $data = $query->get();

        $data->map(function ($finance) {
            $query = StudentCrm::clone()->with([
                'amountsReceived' => function ($q) use ($finance) {
                    $q->select('id_sinh_vien', DB::raw('CAST(thuc_nop AS UNSIGNED) as thuc_thu'), 'so_chung_tu_bien_lai',  DB::raw("DATE_FORMAT(ngay_bien_lai, '%d/%m/%Y') as ngay_xuat_bien_lai"))
                        ->where('dot_hoc_so', $finance->no)
                        ->where('muc_dich_thu', StudentReceivablePurpose::getValueByKey($finance->purpose))
                        ->softDelete();
                },
            ]);
            $studentCRM = $query->select('id')->where('ma_ho_so', $finance->studentProfile->profile_code)->first();

            $query = StudentCrm::clone()->with([
                'amountsReceived' => function ($q) use ($finance) {
                    $q->select('id_sinh_vien', 'muc_dich_thu', DB::raw('SUM(thuc_nop) as total_thuc_nop'))
                        ->where('dot_hoc_so', $finance->no)
                        ->where('muc_dich_thu', StudentReceivablePurpose::getValueByKey($finance->purpose))
                        ->groupBy('muc_dich_thu')
                        ->softDelete();
                },
            ]);
            $totalReceived = $query->select('id')->where('ma_ho_so', $finance->studentProfile->profile_code)->first();

            $finance->amountsReceived = $studentCRM->amountsReceived;
            $finance->totalReceived = $totalReceived->amountsReceived;

            return $finance;
        });

        return $data;
    }

    public function delete(int $id)
    {
        try {
            $now = Carbon::now();
            $credit = FinancialCredit::query()->whereNot('purpose', StudentReceivablePurpose::TUITION_FEE)->findOrFail($id);
            FinancialCredit::query()->findOrFail($id)->delete();
            $transaction = Transaction::query()->findOrFail($credit->transaction_id);
            $code = explode(".", $transaction['code']);
            $code[0] = 'D';
            $code[4] = time();
            $code = implode('.', $code);
            $insert = [
                'code' => $code,
                'student_profile_id' => $transaction['student_profile_id'],
                'is_debt' => 'f',
                'amount' => abs($transaction['amount']),
                'note' => $transaction['note'],
                'approval_status' => $transaction['approval_status'],
                'created_at' => $now,
                'created_by' => auth()->getId(),
            ];
            Transaction::query()->create($insert);
            return (array) 'Delete successful';
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    public function update(int $id, EditRequest $request)
    {
        $request = $request->validated();
        if (!empty($request['note'])) {
            try {
                // $credit = FinancialCredit::query()->findOrFail($id);
                FinancialCredit::query()->findOrFail($id)->update($request);
                // Transaction::query()->findOrFail($credit->transaction_id)->update($request);
                return (array) 'Update successful';
            } catch (\Exception $e) {
                throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
            }
        }
    }

    public function getYearOfPeriods(int $classId, int $semester): int
    {
        $learn_began_date = Period::where('classroom_id', $classId)->where('semester', $semester)->pluck('learn_began_date')->first();
        $year = date('Y', strtotime($learn_began_date));
        return $year;
    }

    public function getCodeClassroom(int $classId): string
    {
        $code = Classroom::where('id', $classId)->pluck('code')->first();
        return $code;
    }

    public function getStudyPlans(int $classId, int $semester)
    {
        $query = StudyPlan::with(['learningModule', 'subject'])->where('classroom_id', $classId)->where('semester', $semester)->orderBy('id', 'asc');
        $studyPlans = $query->get();
        $studyPlans->transform(function ($data) {
            $data->amount_credit = $data->learningModule->amount_credit;
            $data->subjectName = $data->subject->name;
            return $data;
        });
        return $studyPlans;
    }

    public function getDataExport(SearchRequest $request)
    {
        $request  = $request->validated();
        $query = $this->finance_credit_eloquent->clone();
        $query->with(
            [
                'studentProfile' => function ($q) {
                    $q->select('student_profiles.id', 'student_profiles.profile_id', 'student_profiles.profile_code');
                    $q->with('getProfile:id,firstname,lastname,birthday,borned_place,curriculum_vitae');
                },
                'student' => function ($q) {
                    $q->select('students.id', 'students.student_profile_id', 'students.student_code');
                    $q->with([
                        'classroom' => function ($q) {
                            $q->select('classrooms.code', 'classrooms.staff_id', 'classrooms.area_id', 'classrooms.id', 'classrooms.enrollment_object_id');
                            $q->with([
                                'enrollmentObject:id,shortcode',
                                'staff:id,fullname',
                                'area:id,code',
                                'studyPlans' => function ($q) {
                                    $q->select('study_plans.id', 'study_plans.learning_module_id', 'study_plans.subject_id', 'study_plans.classroom_id', 'study_plans.semester');
                                    $q->with([
                                        'learningModule' => function ($q) {
                                            $q->select('learning_modules.id', 'learning_modules.subject_id', 'learning_modules.amount_credit');
                                            $q->with('subject:id,name');
                                        },
                                    ]);
                                },
                                'period' => function ($q) {
                                    $q->whereIn('id', function ($sub) {
                                        $sub->select('p.id')->from('financial_credits as fc')
                                            ->join('students as s', 'fc.student_profile_id', '=', 's.student_profile_id')
                                            ->join('student_classrooms as sc', 'sc.student_id', '=', 's.id')
                                            ->join('classrooms as c', 'sc.classroom_id', '=', 'c.id')
                                            ->join('periods as p', function ($join) {
                                                $join->on('p.classroom_id', '=', 'c.id')
                                                    ->whereRaw('p.semester = CAST(fc.no as SMALLINT)');
                                            })
                                            ->whereRaw('(fc.deleted_time IS NULL OR fc.deleted_time=0)')
                                            ->whereRaw('(s.deleted_time IS NULL OR s.deleted_time=0)')
                                            ->whereRaw('(c.deleted_time IS NULL OR c.deleted_time=0)')
                                            ->groupBy('p.id');
                                    });
                                }
                            ]);
                        },
                        'ignoreLearningModules:id,student_id,learning_module_id'
                    ]);
                }
            ]
        );
        
        $query->whereHas('student', function ($q) {
            $q->where('school_id', school()->getId());
        });

        if (!empty($request['transaction_ids'])) {
            $ids = explode(",", $request['transaction_ids']);
            $query->whereIn('id', $ids);
        }

        if (isset($request['semester'])) {
            $query->where("no",  $request['semester']);
        }

        if (isset($request['purpose']) && $request['purpose'] !== '') {
            $query->where('purpose', $request['purpose']);
        }

        if (isset($request['studentStatus']) && $request['studentStatus'] !== '') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('student_status', $request['studentStatus']);
            });
        }

        if (!empty($request['g_date'])) {
            $period = Period::where('decision_date', $request['g_date'])->get(['classroom_id', 'semester'])->toArray();

            $query->where(function ($q) use ($period) {
                foreach ($period as $key => $value) {
                    if ($key == 0) {
                        $q->where('no', $value['semester'])
                            ->whereHas('student.classroom', function ($sub) use ($value) {
                                $sub->where('classrooms.id', $value['classroom_id']);
                            });
                    } else {
                        $q->orWhere(function ($q) use ($value) {
                            $q->where('no', $value['semester'])
                                ->whereHas('student.classroom', function ($sub) use ($value) {
                                    $sub->where('classrooms.id', $value['classroom_id']);
                                });
                        });
                    }
                }
            });
        }

        if (!empty($request['receivable_date'])) {
            $period = Period::where('collect_began_date', $request['receivable_date'])->get(['classroom_id', 'semester'])->toArray();

            $query->where(function ($q) use ($period) {
                foreach ($period as $key => $value) {
                    if ($key == 0) {
                        $q->where('no', $value['semester'])
                            ->whereHas('student.classroom', function ($sub) use ($value) {
                                $sub->where('classrooms.id', $value['classroom_id']);
                            });
                    } else {
                        $q->orWhere(function ($q) use ($value) {
                            $q->where('no', $value['semester'])
                                ->whereHas('student.classroom', function ($sub) use ($value) {
                                    $sub->where('classrooms.id', $value['classroom_id']);
                                });
                        });
                    }
                }
            });
        }

        if (!empty($request['staff'])) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('classroom', function ($sub) use ($request) {
                    $sub->where('staff_id', $request['staff']);
                });
            });
        }

        if (!empty($request['classId'])) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('classroom', function ($sub) use ($request) {
                    $sub->where('classrooms.id', $request['classId']);
                });
            });
        }

        if (!empty($request['fullname'])) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->whereHas('getProfile', function ($sub) use ($request) {
                    $sub->where(DB::raw("lower(CONCAT(firstname, ' ', lastname))"), "ilike", $request['fullname']);
                });
            });
        }

        if (!empty($request['student_code'])) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where("profile_code", 'ilike', $request['student_code']);
            });

            $query->orWhereHas('student', function ($q) use ($request) {
                $q->where("student_code", 'like', $request['student_code']);
            });
        }
        
        if (RoleAuthority::LEARNING_MANAGEMENT()->check()) {
            if (!RoleAuthority::PM()->check()) {
                $query->whereHas('student.classrooms', function ($q) {
                    $q->where("staff_id", auth()->user()->getStaffId());
                });
            }
        }

        $query->orderBy('id', 'desc');
        $now = Carbon::now();
        $price = CreditPrice::where('effective_date', '=', function ($subquery) use ($now) {
            $subquery->select(DB::raw('MAX(effective_date)'))
                ->from('credit_prices')
                ->where('effective_date', '<=', $now);
        })->pluck('price')->first();

        $data = $query->get();
        $data->transform(function ($finance) use ($price) {
            $totalAmount = 0;
            $export = [
                'code' => $finance->student->student_code ?? '',
                'firstname' => $finance->studentProfile->getProfile->firstname,
                'lastname' => $finance->studentProfile->getProfile->lastname,
                'birthday' => date('d/m/Y', strtotime($finance->studentProfile->getProfile->birthday)),
                'borned_place' => $finance->studentProfile->getProfile->borned_place,
                'object' => $finance->student->classroom->enrollmentObject->shortcode,
            ];
            foreach ($finance->student->classroom->studyPlans->where('semester', $finance->no) as $data) {
                $export[$data->learningModule->id] = $data->learningModule->amount_credit;
                $totalAmount += $data->learningModule->amount_credit;
            }

            $export['totalAmount'] = $totalAmount;
            $export['price'] = $price;
            $export['tuition'] = $totalAmount * $price;
            $export['diff'] = 0;
            $export['receive'] = $export['tuition'] - $export['diff'];
            $export['note'] = $finance->note ?? '';
            return $export;
        });
        return $data;
    }
}
