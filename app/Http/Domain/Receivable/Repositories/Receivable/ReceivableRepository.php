<?php

namespace App\Http\Domain\Receivable\Repositories\Receivable;

use App\Eloquent\ClassroomReceivable;
use App\Eloquent\Classroom as EloquentClassroom;
use App\Eloquent\StudentReceivable as EloquentStudentReceivable;
use App\Eloquent\ClassroomReceivable as EloquentClassroomReceivable;
use App\Http\Domain\Receivable\Models\ClassroomReceivable\ClassroomReceivable as ClassroomReceivableModel;
use App\Eloquent\Period;
use App\Eloquent\Major as EloquentMajor;
use App\Eloquent\Staff as EloquentStaff;
use App\Eloquent\Student as EloquentStudent;
use App\Http\Enum\ReceivablePurpose;
use  App\Http\Enum\StudentStatus;
use  App\Http\Enum\StaffTeam;
use Illuminate\Http\JsonResponse;
use App\Http\Domain\Receivable\Requests\Receivable\SearchRequest;
use App\Http\Domain\Receivable\Requests\Receivable\ClassroomReceivableRequest;
use App\Http\Domain\Receivable\Requests\Receivable\CreateStudentReceivableRequest;
use App\Http\Domain\Receivable\Requests\Receivable\EditStudentReceivableRequest;
use App\Http\Enum\ReceivableStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;


/**
 * Class ReceivableRepository
 * @package App\Http\Domain\Receivable\Repositories\Receivable
 */
class ReceivableRepository implements ReceivableRepositoryInterface
{

    public function __construct()
    {

    }

    public function fetchPeriod() 
    {
        $query = Period::query()->getQuery();
        $periods =  $query->get(['periods.id', 'periods.semester', 'periods.classroom_id', 'periods.collect_began_date'])->unique()->toArray();
        return $periods;
    }

    public function getAllQlht()
    {
        $qlht = EloquentStaff::query()->newQuery()
            ->where('staffs.team', StaffTeam::LEARNING_MANAGEMENT)// 3 quản lý học tập
            ->distinct()
            ->get(['staffs.id', 'staffs.fullname'])
            ->toArray();
        return response()->json(['data' => $qlht]);
    }

    public function getAllMajor() 
    {
        $majors = EloquentMajor::query()->newQuery()
            ->get(['majors.id', 'majors.shortcode', 'majors.name'])
            ->toArray();

        return response()->json(['data' => $majors]);
    }

    public function getAllClassroom() 
    {
        $classrooms = EloquentClassroom::query()->newQuery()
            ->get(['classrooms.id', 'classrooms.major_id', 'classrooms.code', 'classrooms.area_id', 'classrooms.staff_id'])
            ->toArray();

        return response()->json(['data' => $classrooms]);
    }

    public function getAll(SearchRequest $request)
    {
        $per_page = $request->perPage();
        $request = $request->validated();
        $query = $this->classroomReceivableQuery();
        $query->with(['classroom' => function ($q){
            $q->with(['period', 'staff', 'major', 'studentClassrooms']); 
        }]);
        if (!empty($request['began_date']) && $request['began_date'] !== 'all') {
            $query->when($request['began_date'], function ($q) use ($request) {
                $q->whereHas('classroom.period', function ($q2) use ($request) {
                    $q2->where('periods.collect_began_date', '=', $request['began_date']);
                });
            });
        }
        if(!empty($request['lop_quan_ly']) && $request['lop_quan_ly'] !== 'all' && is_numeric($request['lop_quan_ly']))
        {
            $class = trim($request['lop_quan_ly']);
            $query->when($request, function ($q) use ($class) {
                $q->whereHas('classroom', function ($q2) use ($class) {
                    $q2->where('classrooms.id', '=', $class);
                });
            });
        }
        if(!empty($request['dot_hoc']) && $request['dot_hoc'] !== 'all' && is_numeric($request['dot_hoc']))
        {
            $query->when($request, function ($q) use ($request) {
                $q->where('semester', '=', $request['dot_hoc']);
            });
        }
        if(!empty($request['ma_nganh']) && $request['ma_nganh'] !== 'all' && is_numeric($request['ma_nganh']))
        {
            $query->when($request, function ($q) use ($request) {
                $q->whereHas('classroom.major', function ($q2) use ($request) {
                    $q2->where('majors.id', '=', $request['ma_nganh']);
                });
            });
        }
        if(!empty($request['quan_ly_hoc_tap']) && $request['quan_ly_hoc_tap'] !== 'all' && is_numeric($request['quan_ly_hoc_tap']))
        {
            $query->when($request, function ($q) use ($request) {
                $q->whereHas('classroom.staff', function ($q2) use ($request) {
                    $q2->where('staffs.id', '=', $request['quan_ly_hoc_tap']);
                });
            });
        }
        // dd($request);
        $data = $query->paginate($per_page);
        $data->getCollection()->transform(function ($receivable) {
            return new ClassroomReceivableModel($receivable);
        });
        return $data;
    }

    /**
     * @param int $id
     * @return array
     */
    public function findClassroomReceivable(int $id)
    {
        try {
            /** @var null|EloquentClassroomReceivable $classroom_receivable */
            $classroom_receivable = $this->classroomReceivableQuery()
                ->with(['classroom' => function ($query) {
                    /** @var Builder $query */
                    $query->with(['students' => function ($query) {
                        /** @var Builder $query */
                        $query->with(['studentProfile' => function ($query) {
                            /** @var Builder $query */
                            $query->with([
                                'studentReceivables' => function ($query) {
                                    $query->where('purpose', ReceivablePurpose::TUITION_FEE);
                                },
                                'getProfile' => function ($query) {
                                    /** @var Builder $query */
                                },
                            ])
                                ->select('*');
                        }])
                        ->whereIn('student_status', StudentStatus::studentInClass())
                        ->select('*');
                    }, 'area:id,code,name', 'enrollmentWave:first_day_of_school,id', 'staff:id,fullname'])
                        ->select('*');

                }])
                ->find($id);

                // dd($classroom_receivable);
            return  $classroom_receivable;
        } catch (\Exception $exception) {
            throw new HttpResponseException(response()->json([
                'traces' => $exception->getTrace(),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]));
        }
    }

    public function storeStudentReceivable(CreateStudentReceivableRequest $request) 
    {
        $request->throwJsonIfFailed();
        $result = false;
        try {
            $result = DB::transaction(function () use ($request) {
                if (!$request->student_receivable_id) {
                    dd(12);
                    // $student_receivable = $this->studentReceivableQuery()
                    //     ->create([
                    //         'student_profile_id' => $request->student_profile_id,
                    //         'receivable' => $request->receivable,
                    //         'purpose' => $request->purpose,
                    //         'learning_wave_number' => $request->learning_wave_number,
                    //         'note' => $request->note ?? '',
                    //     ]);
                }else{
                    dd(23); 
                    // $student_receivable = $this->studentReceivableQuery()->findOrFail($request->student_receivable_id);
                    // $student_receivable->update([
                    //     'receivable' => $request->receivable,
                    //     'll' => $request->note ?? '',
                    // ]); 
                }

                return true;
            });
        } catch (\Exception $exception) {
            throw new HttpResponseException(response()->json([
                'traces' => $exception->getTrace(),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]));
        }

        return $result;
    }

    public function updateStudentReceivable(EditStudentReceivableRequest $request) 
    {
        $request->throwJsonIfFailed();
        $result = false;
        try {
            $result = DB::transaction(function () use ($request) {
                if ($request->student_receivable_id) {
                    $student_receivable = $this->studentReceivableQuery()->findOrFail($request->student_receivable_id);
                    $student_receivable->update([
                        'receivable' => $request->receivable,
                        'note' => $request->note ?? '',
                    ]);
                }
                return true;
            });
        } catch (\Exception $exception) {
            throw new HttpResponseException(response()->json([
                'traces' => $exception->getTrace(),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]));
        }

        return $result;
    }


    public function storeClassroomReceivable(ClassroomReceivableRequest $request)
    {
        $request->throwJsonIfFailed();
        $result = false;
        try {
            $result = DB::transaction(function () use ($request) {

                $insert_input = array_filter($request->input, function ($input) {
                    return !isset($input['classroom_receivable_id']) || !$input['classroom_receivable_id'];
                });

                $update_input = array_filter($request->input, function ($input) {
                    return isset($input['classroom_receivable_id']) && $input['classroom_receivable_id'];
                });

                if (!empty($insert_input)) {
                    $began_date = $request->began_date;
                    $semesters = array_column($insert_input, 'semester');
                    $classroom_ids = array_column($insert_input, 'classroom_id');
                    $purposes = array_column($insert_input, 'purpose');
                    $this->createClassroomReceivable($began_date, $semesters, $classroom_ids, $purposes, $insert_input);
                }

                if (!empty($update_input)) {
                    $this->updateClassroomReceivable($update_input);
                }

                // dd(EloquentClassroomReceivable::all()->toArray());

                $classroom_id = $request->input[0]['classroom_id'] ?? null;
                $semester = $request->input[0]['semester'] ?? null;

                if ($classroom_id) {
                    $students = $this->getStudentClassrooms($semester, $classroom_id);
                    // dd($students);
                    return $students;
                }
            });
        } catch (\Exception $exception) {
            throw new HttpResponseException(response()->json([
                'traces' => $exception->getTrace(),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]));
        }

        return $result;

    }

    public function insertOrUpdateStudentReceivable($student_receivable_attributes, $update_data) 
    {
        if (!empty($student_receivable_attributes)) {
            $this->studentReceivableQuery()->insert($student_receivable_attributes);
        }

        if (!empty($update_data)) {
            $sql_update_student_receivable = 'update  student_receivables set receivable = u.receivable from (values ' .
                implode(', ', array_map(function ($item) {
                    return "({$item['id']}, {$item['receivable']})";
                }, $update_data)) .
                ') as u(id, receivable) where student_receivables.id = u.id';

            DB::statement($sql_update_student_receivable);
        }
    }



    public function  getStudentClassrooms ($semester, $classroom_id) 
    {
        $students = EloquentStudent::query()
                    ->with(['studentProfile' => function ($query) use ($semester) {
                        /** @var Builder $query */
                        $query->with(['studentReceivables' => function ($query) use ($semester) {
                            /** @var Builder $query */
                            $query->where('purpose', ReceivablePurpose::TUITION_FEE)
                                ->where('learning_wave_number', $semester)
                                ->select(['id', 'student_profile_id', 'purpose', 'learning_wave_number']);
                        }])
                            ->select('id');
                    }])
                    ->whereRelation('getStudentClassroom', function ($query) use ($classroom_id) {
                        $query->where('classroom_id', '=', $classroom_id);
                    })
                    ->whereIn('student_status', StudentStatus::studentInClass())
                    ->get(['student_profile_id']);
        return $students;

    }

    private function createClassroomReceivable(Carbon $began_date, array $semesters, array $classroom_ids, array $purposes, array $data): bool
    {
        $now = Carbon::now();

        $insert_input = array_map(function ($attribute) use ($now, $began_date) {
            $attribute['began_date'] = $began_date;
            $attribute['created_at'] = $now;

            if (isset($attribute['so_tien'])) {
                $attribute['fee'] = $attribute['so_tien'];
                unset($attribute['so_tien']);
            }

            return $attribute;
        }, $data);

        return $this->classroomReceivableQuery()->insert($insert_input);
    }

    private function updateClassroomReceivable(array $data): bool
    {
        $sql_update_classroom_receivables = 'update  classroom_receivables set fee = u.fee from (values ' .
            implode(', ', array_map(function ($item) {
                return "({$item['classroom_receivable_id']}, {$item['so_tien']})";
            }, $data)) .
            ') as u(id, fee) where classroom_receivables.id = u.id';
        // dd($sql_update_classroom_receivables);

        return DB::statement($sql_update_classroom_receivables);
    }

    private function classroomReceivableQuery()
    {
        return EloquentClassroomReceivable::query()->newQuery();
    }

    public function classroomQuery()
    {
        return EloquentClassroom::query()->newQuery();
    }

    private function studentReceivableQuery()
    {
        return EloquentStudentReceivable::query()->newQuery();
    }
}