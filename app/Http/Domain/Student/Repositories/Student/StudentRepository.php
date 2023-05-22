<?php

namespace App\Http\Domain\Student\Repositories\Student;

use App\Eloquent\Area;
use App\Eloquent\Classroom;
use App\Eloquent\Crm\Student as StudentCrm;
use App\Eloquent\Grade;
use App\Eloquent\Profile;
use App\Eloquent\Student;
use App\Eloquent\Staff;
use App\Eloquent\StudentReceivable;
use App\Eloquent\Period;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Student\Models\Grade\Grade as GradeModel;
use App\Http\Domain\Student\Models\Student\Student as StudentModel;
use App\Http\Domain\Student\Requests\Student\SearchRequest;
use App\Http\Domain\Student\Requests\Student\G110SearchRequest;
use App\Http\Domain\Student\Services\StudentReceivablesService;
use App\Http\Enum\ReceivablePurpose;
use App\Http\Enum\RoleAuthority;
use App\Http\Enum\TuitionFee;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ReflectionException;
use Throwable;


/**
 * Class StudentRepository
 * @package App\Http\Domain\Student\Repositories\Area
 */
class StudentRepository implements StudentRepositoryInterface
{
    use ThrowIfNotAble;

    /**
     * @var Builder|Model
     */
    private Builder|Model $model_eloquent;
    private StudentReceivablesService $student_receivables_service;
    private string $model;

    /**
     *
     */
    public function __construct(StudentReceivablesService $student_receivables_service)
    {
        $this->model                       = Student::class;
        $this->model_eloquent              = Student::query()->getModel();
        $this->student_receivables_service = $student_receivables_service;
    }

    /**
     * @param SearchRequest $request
     * @param bool $get_all
     * @return \App\Eloquent\Model[]|LengthAwarePaginator|Builder[]|Collection|Model[]|mixed|object
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function getAll(SearchRequest $request, bool $get_all = false): mixed
    {
        $per_page = $request->perPage();
        $request  = $request->validated();

        $query = $this->model_eloquent->clone();

        $query->with([
            'classroom'      => function ($q) {
                $q->with([
                    'major:id,code,name',
                    'area:id,name,school_id',
                    'enrollmentWave:id,school_id,area_id,first_day_of_school',
                    'staff:id,fullname',
                ]);
            },
            'studentProfile' => function ($q) {
                $q->with('profile');
            },
        ]);

        if (RoleAuthority::LEARNING_MANAGEMENT()->check()) {
            $query->whereExists(function ($query) {
                /** @var Builder $query */
                $query->selectRaw('1')->from('student_classrooms')
                    ->join('classrooms', 'student_classrooms.classroom_id', '=', 'classrooms.id')
                    ->where(function ($query) {
                        /** @var Builder $query */
                        $query->orWhereNull('student_classrooms.ended_at');
                        $query->orWhere(function ($query) {
                            /** @var Builder $query */
                            $query->whereDate('student_classrooms.began_at', '>=', Carbon::now());
                            $query->whereDate('student_classrooms.ended_at', '>=', Carbon::now());
                        });
                    })
                    ->whereRaw('student_classrooms.student_id=students.id');

                if (!RoleAuthority::PM()->check()) {
                    $query->where('classrooms.staff_id', auth()->user()->getStaffId());
                }
            });
        }

        if (!empty($request['student_code'])) {
            $student_code = trim(mb_strtolower($request['student_code'], 'UTF-8'));
            $query->whereRaw('lower(student_code) LIKE ?', ["%$student_code%"]);
        }
        if (!empty($request['account'])) {
            $account = trim(mb_strtolower($request['account'], 'UTF-8'));
            $query->whereRaw('lower(account) LIKE ?', ["%$account%"]);
        }
        if (!empty($request['area']) && $request['area'] !== 'all' && is_numeric($request['area'])) {
            $area = trim($request['area']);
            $query->when($area, function ($q) use ($area) {
                $q->whereRelation('classroom.area', 'id', '=', "$area");
            });
        }
        if (!empty($request['first_day_of_school'])) {
            $first_day_of_school = trim(mb_strtolower($request['first_day_of_school'], 'UTF-8'));
            $first_day_of_school = date('Y-m-d', strtotime($first_day_of_school));
            $query->when($first_day_of_school, function ($q) use ($first_day_of_school) {
                $q->whereRelation('classroom.enrollmentWave', DB::raw("DATE(first_day_of_school)"), "=", $first_day_of_school);
            });
        }
        if (!empty($request['staff']) && $request['staff'] !== 'all' && is_numeric($request['staff'])) {
            $staff = trim($request['staff']);
            $query->when($staff, function ($q) use ($staff) {
                $q->whereRelation('classroom.staff', 'id', "=", $staff);
            });
        }
        if (!empty($request['class'])) {
            $class = trim($request['class']);
            $query->when($class, function ($q) use ($class) {
                $q->whereHas('classrooms', function ($q2) use ($class) {
                    $q2->whereIn('classrooms.id', explode(',', $class));
                });
            });
        }
        if (array_key_exists('student_status', $request) && !is_null($request['student_status'])) {
            $student_status = trim($request['student_status']);
            $query->whereIn('student_status', explode(',', $student_status));
        }
        if (!empty($request['fullname'])) {
            $fullname = trim(mb_strtolower($request['fullname'], 'UTF-8'));
            $query->when($fullname, function ($q) use ($fullname) {
                $q->whereRelation('studentProfile.profile', DB::raw("lower(CONCAT(firstname, ' ', lastname))"), "LIKE", "%$fullname%");
            });
        }
        if (!empty($request['profile_code'])) {
            $profile_code = trim(mb_strtolower($request['profile_code'], 'UTF-8'));
            $query->when($profile_code, function ($q) use ($profile_code) {
                $q->whereRelation('studentProfile', DB::raw("lower(profile_code)"), "LIKE", "%$profile_code");
            });
        }
        if (!empty($request['phone_number'])) {
            $phone_number = trim($request['phone_number']);
            $query->when($phone_number, function ($q) use ($phone_number) {
                $q->whereRelation('studentProfile.profile', 'phone_number', "LIKE", "$phone_number%");
            });
        }
        if(!empty($request['major'])  && $request['major'] != 'all' && $request['major'] != null)
        {
            $major = $request['major'];
            // dd($major);
            $query->whereHas('classroom', function ($q1) use ($major) {
                $q1->whereHas('major', function($q2) use ($major) {
                    $q2->where('id', $major);   
                });
            });
        }
        $query->orderByDesc('id');
        if ($get_all) {
            $data = $query->get()->transform(function ($student) {
                return new StudentModel($student);
            });
        } else {
            $data = $query->makePaginate($per_page);
            $data->getCollection()->transform(function ($student) {
                return new StudentModel($student);
            });
        }
        return $data;
    }


    /**
     * @param int $id
     * @return StudentModel
     */
    public function getById(int $id): StudentModel
    {
        $query = $this->model_eloquent->clone();
        $query->select('id', 'school_id', 'student_profile_id', 'student_code', 'account', 'email', 'student_status', 'profile_status', 'note');

        $query->with([
            'studentProfile' => function ($q) {
                $q->select('id', 'profile_code', 'staff_id', 'profile_id', 'documents');
                $q->with(['staff:id,fullname', 'profile']);
            },
            'school:id,school_code,school_name',
            'classroom'      => function ($q) {
                $q->with([
                    'enrollmentWave:id,school_id,area_id,first_day_of_school',
                    'major:id,code,name',
                ]);
            },
        ]);
        $data = $query->findOrFail($id);

        return new StudentModel($data);
    }

    /**
     * @param int $id
     * @return Collection|array
     */
    public function getGradesById(int $id): Collection|array
    {
        $query = Grade::query()->clone();

        $query->select('id', 'student_id', 'learning_module_id', 'exam_date', 'note', 'ipkey');
        $query->with([
            'learningModule' => function ($q) {
                $q->select([
                    'learning_modules.id',
                    'learning_modules.subject_id',
                    'learning_modules.code',
                    'learning_modules.amount_credit',
                    'subjects.name AS subject_name',
                    'grade_setting_div',
                ]);
                $q->join('subjects', 'learning_modules.subject_id', '=', 'subjects.id');
                $q->groupBy('learning_modules.id', 'subjects.name', 'learning_modules.subject_id', 'learning_modules.code', 'learning_modules.amount_credit');
            },
            'gradeValues']);
        $query->where('student_id', $id);
        $data = $query->get();

        $data->transform(function ($grade) {
            return new GradeModel($grade);
        });

        return $data;
    }

    /**
     * @param int $id
     * @return array
     * @throws ReflectionException
     */
    public function getTuitionById(int $id): array
    {
        $student = $this->model_eloquent->newQuery()->with([
            'studentProfile',
            'financialCredits:id,student_profile_id,amount,purpose',
        ])->find($id);

        $profile_code = $student->studentProfile->profile_code;

        $student_crm = StudentCrm::query()->clone()->with([
            'amountsReceived' => function ($q) {
                $q->softDelete();
            },
        ])->where('ma_ho_so', $profile_code)->first();

        $amounts_received = $student_crm->amountsReceived ?? [];

        $student_receivables = StudentReceivable::query()
            ->whereHas('studentProfile', function ($q) use ($profile_code) {
                $q->where('profile_code', $profile_code);
            })->orderBy('learning_wave_number')->get();

        $purposes = $this->student_receivables_service->mappingPurpose($student_receivables);

        return $this->student_receivables_service->mappingAmountsReceivedToPurpose($amounts_received, $purposes);
    }

    /**
     * @param int $id
     * @return Collection|array
     */
    public function getProfile(int $id): Collection|array
    {
        $query = Profile::query()->clone();
        $query->whereHas('student', function ($q) use ($id) {
            $q->where('students.id', $id);
        });
        return $query->get();
    }

    public function updateStudentProfile(int $id, array $data): bool
    {
        return $this->updateAble($this->model, function () use ($id, $data) {
            /** @var \App\Eloquent\Student $student */
            $student = $this->model_eloquent->newQuery()->with('studentProfile')->findOrFail($id);
            return (bool)$student->studentProfile()->update($data);
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ReflectionException
     * @throws Throwable
     */
    public function update(int $id, array $data): bool
    {
        return $this->updateAble($this->model, function () use ($id, $data) {
            return $this->model_eloquent->newQuery()->findOrFail($id)->updateOrFail($data);
        });
    }

    /**
     * getListItems
     *
     * @return array
     */
    public function getListItems(): array
    {
        return $this->model_eloquent->get()->toArray();
    }


    /**
     * @param array $student_codes
     * @param array $get
     * @return array|Collection
     */
    public function getByStudentCode(array $student_codes, array $get = ['*']): array|Collection
    {
        return $this->model_eloquent->newQuery()->whereIn('student_code', $student_codes)->get($get);
    }

    public function getByStudentProfileId(int $student_profile_id)
    {
        return $this->model_eloquent->query()
            ->where('student_profile_id',$student_profile_id)->get();
    }

    public function getDataG110(G110SearchRequest $request): mixed
    {
        $request  = $request->validated();
        $qlht = '';
        $first_day_of_school = (!empty($request['first_day_of_school']) && $request['first_day_of_school']) ? $request['first_day_of_school'] : '';
        $area = !empty($request['area']) ? $request['area'] : '';
        
        $query = Classroom::query()
            ->with('major:id,name', 'area:id,name', 'enrollmentWave', 'staff');
        
        if (isset($request['area']) && !empty($request['area']) && $request['area'] !== 'all' && is_numeric($request['area'])) {
            $query->whereHas('area', function ($query) use ($request) {
                $query->where('id', $request['area']);
            });
            $area = Area::where('id', $request['area'])->get()->first()->code;
        }

        if (isset($request['first_day_of_school']) && !empty($request['first_day_of_school'])  && $request['first_day_of_school'] != 'all' && $request['first_day_of_school'] != null) {
            $first_day_of_school = trim(mb_strtolower($first_day_of_school, 'UTF-8'));
            $first_day_of_school = date('Y-m-d', strtotime($first_day_of_school));
            $query->whereHas('enrollmentWave', function ($query) use ($first_day_of_school) {
                $query->where('first_day_of_school', $first_day_of_school);
            });
        }
    
        if (isset($request['staff']) && !empty($request['staff']) && $request['staff'] !== 'all' && is_numeric($request['staff']))
        {
            $staff = trim($request['staff']);
            $query->whereHas('staff', function ($query) use ($staff) {
                $query->where('id', $staff);
            });
            $qlht = Staff::where('staffs.id', $request['staff'])->get()->first()->fullname;
        }
        if(isset($request['major']) && !empty($request['major'])  && $request['major'] != 'all' && $request['major'] != null)
        {
            $major = $request['major'];
            $query->whereHas('major', function ($query) use ($major) {
                $query->where('id', $major);
            });
        }
        $class_ids = $query->get()->pluck('id')->toArray();
        $student_status = (isset($request['student_status']) && !empty($request['student_status'])  && $request['student_status'] != 'all' && $request['student_status'] != null) ? explode(',',$request['student_status']) : [];
        // dd($query);
       

        $student_query = Student::query()->with(['studentProfile' => function($q) {
            $q->with(['studentReceivables' => function($q) {
                $q->whereIn('learning_wave_number',[0,1])
                  ->whereIn('purpose',[ReceivablePurpose::ADMISSION_FEE,ReceivablePurpose::TUITION_FEE])
                  ->where(function($q) {
                    $q->where('reference_table','financial_credits')
                      ->orWhere('reference_table','classroom_receivables');
                  });
            }]);
        },'studentClassrooms' => function($q) {
            $q->with(['getClassroom' => function($q) {
                $q->with('area','major','enrollmentWave','staff');
            }]);
        }])->when(!empty($student_status),function($q) use($student_status){
            $q->whereIn('student_status',$student_status);
        })->when(!empty($class_ids),function($q) use($class_ids){
            $q->whereHas('studentClassrooms',function ($q) use($class_ids) {
                $q->whereIn('classroom_id',$class_ids);
            });
        });

        return [ $student_query->get(), $first_day_of_school, $qlht, $area];
    }

    public function getThucThu($students)
    {
        $profile_codes = $students->map(function ($student) {
            return ['id' => $student->id, 'code' => $student->studentProfile->profile_code];
        })->pluck('code', 'id')->toArray();

        $query = StudentCrm::query()->getModel();
        $sinhViens = $query->with(['amountsReceived' => function($query){
            $query->where(function ($query) {
                    $query->orWhere('deleted', 0);
                    $query->orWhereNull('deleted');
                });
            $query->where('dot_hoc_so', 1)->orWhereNull('dot_hoc_so');

        }])->whereIn('ma_ho_so', $profile_codes)
           ->get()
           ->transform(function($sv) {
                $thuc_thu = [
                    'hoc_phi' => 0,
                    'lpxt' => 0
                ];
                if(empty($sv->amountsReceived))
                    return [];
                foreach($sv->amountsReceived as $thucthu)
                {
                    if($thucthu->muc_dich_thu == TuitionFee::ADMISSION_FEE)
                    {
                        $thuc_thu['lpxt'] += $thucthu->thuc_nop;
                    }else if($thucthu->muc_dich_thu == TuitionFee::FREE) {
                        
                        $thuc_thu['hoc_phi'] += $thucthu->thuc_nop;
                    }
                }
                // if($sv->ma_ho_so == 'TVU010302')
                //     dd($sv->amountsReceived);
                return [$sv->ma_ho_so => $thuc_thu];
           });
        return call_user_func_array('array_merge',array_map(function($tt) {
            return $tt;
        },$sinhViens->toArray()));
    }

}