<?php

namespace App\Http\Domain\Reports\Repositories\G820;

use App\Eloquent\Student;
use App\Eloquent\Period;
use App\Eloquent\Staff;
use App\Eloquent\Major;
use App\Eloquent\Classroom;
use App\Eloquent\StudentRevisionHistory;
use App\Eloquent\Crm\Student as CrmStudent;
use App\Eloquent\StudentProfile;
use App\Eloquent\StudentClassroom;
use App\Eloquent\StudentReceivable;
use App\Http\Enum\ReceivablePurpose;
use App\Http\Enum\StudentRevisionHistoryType;
use App\Http\Enum\StudentReceivablePurpose;
use App\Http\Enum\StudentTypeWeek;
use App\Http\Enum\TuitionFee;
use Illuminate\Pagination\LengthAwarePaginator;

class G820Repository implements G820RepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll($request)
    {
        $semester = '';
        $query = Period::query()->with(['classroom' => function($q) {
            $q->with('staff','period');
        }]);

        if(array_key_exists('g_date',$request) && $request['g_date'] != '')
        {
            $query->where(function($q) use ($request) {
                $q->where('decision_date', $request['g_date'])->orWhere(function ($q) use ($request) {
                        $q->whereNull('decision_date')->where('learn_began_date', $request['g_date']);
                });
            });
        }
        
        $all_dot_hoc = [];

        if(array_key_exists('semester',$request) && $request['semester'] != '')
        {
            $semester = (int)$request['semester'];
            $all_dot_hoc = range(1,$semester);
            $query->where('semester',$semester);
        }

        if(array_key_exists('classes',$request) && $request['classes'] != '')
        {
            if(gettype($request['classes']) === 'string')
            {
                $query->whereHas('classroom', function ($q) use ($request){
                    $q->whereIn('classrooms.id', explode(',',$request['classes']));
                });
            } else {
                $query->whereHas('classroom', function ($q) use ($request){
                    $q->whereIn('classrooms.id', $request['classes']);
                });
            }
        }

        if(array_key_exists('major',$request) && $request['major'] != '')
        {
            // $major_id = Major::query()->where('id', $request['major'])->get()->first()->id;
            $query->whereHas('classroom', function ($q) use ($request){
                $q->where('major_id', $request['major']);
            });
        }
        if(array_key_exists('staff',$request) && $request['staff'] != '')
        {
            $query->whereHas('classroom',function($q) use($request) {
                $q->whereHas('staff',function($q) use($request) {
                    $q->where('id',$request['staff']);
                });
            });
        }

        $getClass = $query->pluck('classroom_id');

        $records = Student::query()
           ->with(['studentProfile' => function($q) {
                $q->with('receivable');
           },'getProfile','classrooms' => function ($q) {
                $q->with('major','staff','enrollmentObject');
           },'studentClassrooms' => function ($q) use ($getClass){
                $q->whereIn('classroom_id',$getClass);
           },'learningEngagement'])
           ->whereHas('studentClassrooms',function($q) use ($getClass){
                $q->whereIn('classroom_id',$getClass);
           })
           ->whereHas('learningEngagement',function ($q) {
                $q->whereIn('student_type_fourth_week',StudentTypeWeek::qualifiedStudents());
           })
           ->whereNotNull('student_status')
        //   ->whereIn('student_status',StudentStatus::studentInClass())
        //   ->whereHas('getStudentReceivables', function ($q) use ($all_dot_hoc){
        //       $q->whereIn('learning_wave_number',$all_dot_hoc);
        //   })
          ->orderByRaw('(select p.id from majors AS p inner join classrooms sp on sp.major_id = p.id inner join student_classrooms sc on sc.classroom_id = sp.id where sc.student_id = students.id)')
          ->orderByRaw('(select cl.id from classrooms cl inner join student_classrooms sc on sc.classroom_id = cl.id where sc.student_id = students.id)')
          ->orderByRaw('(select p.lastname from profiles AS p inner join student_profiles sp on sp.profile_id = p.id where sp.id = students.student_profile_id)');

        $students = $records->get();

        $studentIdLists             = $students->pluck('id');
        $semester_lists             = Period::query();
        if($all_dot_hoc != [])
        {
            $semester_lists->whereIn('semester',$all_dot_hoc);
        }
        $semester_lists = $semester_lists->get();
        
        $student_status_changes     = StudentRevisionHistory::query()->where('type',2)
                                                                     ->whereIn('student_id',$studentIdLists)
                                                                     ->get()
                                                                     ->toArray();

        return [
            // 'data'                      => $records->paginate($limit),
            'data'                      => isset($request['per_page']) ? $records->makePaginate($request['per_page']) : $records->get(),
            'semester'                  => $semester,
            'all_students'              => $students,
            'semester_list'             => $semester_lists,
            'student_status_changes'    => $student_status_changes,
        ];
    }

    public function getThucThu($students)
    {
        $profile_codes = $students->map(function ($student) {
            /** @var EloquentStudent $student */
            return ['id' => $student->id, 'code' => $student->studentProfile->profile_code];
        })->pluck('code', 'id')->toArray();

        $query = CrmStudent::query()->getModel();
        $CrmStudents = $query->with(['amountsReceived' => function($query){
            /** @var Builder $query */
            $query->where('muc_dich_thu', StudentReceivablePurpose::getValueByKey(1))
                  ->where(function ($query) {
                      /** @var Builder $query */
                      $query->orWhere('deleted', 0);
                      $query->orWhereNull('deleted');
                  });

        }])->whereIn('ma_ho_so', $profile_codes)
           ->get()->transform(function ($sv) {
            /** @var CrmStudent $sv */
            $thuc_thu = [];

            foreach($sv->amountsReceived as $thucthu)
            {
                if(!isset($thuc_thu[$sv->ma_ho_so]['semester_'.$thucthu->dot_hoc_so]))
                {
                    $thuc_thu[$sv->ma_ho_so]['semester_'.$thucthu->dot_hoc_so] = $thucthu->thuc_nop;
                } else {
                    $thuc_thu[$sv->ma_ho_so]['semester_'.$thucthu->dot_hoc_so] += $thucthu->thuc_nop;
                }
            }

            $thuc_thu[$sv->ma_ho_so] = array_map(function($tt) {
                return $tt;
            },$thuc_thu[$sv->ma_ho_so]);
            return $thuc_thu;;
        });
        
        return $CrmStudents->toArray();
    }

    /**
     * \Illuminate\Database\Eloquent\Collection $students
    */
    public function getPhaiThu($students,$all_dot_hoc): array
    {
        $profile_codes = $students->map(function ($student) {
            /** @var EloquentStudent $student */
            return ['code' => $student->studentProfile->profile_code];
        })->pluck('code')->toArray();

        $student_profiles = StudentProfile::whereIn('profile_code', $profile_codes)->get();
        $student_profile_ids = $student_profiles->pluck('id')->toArray();

        $student_receivables = StudentReceivable::query()->with(['student' => function($q) {
                                                            $q->with('studentProfile'); 
                                                         }])
                                                         ->whereIn('student_profile_id',$student_profile_ids)
                                                         ->where('purpose', StudentReceivablePurpose::getValueByKeyVi('HOC_PHI'))
                                                         ->whereIn('learning_wave_number',$all_dot_hoc)
                                                         ->get();

        $phai_thu = [];
        foreach($student_profiles as $student_profile)
        {
            $receivables = call_user_func_array('array_merge',$student_receivables->where('student_profile_id',$student_profile->id)->transform(function($receivable) {
                return [
                    'semester'            => $receivable->learning_wave_number,
                    'code'                => $receivable->student->studentProfile->profile_code,
                    "receivable"          => $receivable->receivable
                ];
            })->toArray());
            array_push($phai_thu,$receivables);
        }
        
        return array_filter($phai_thu);
    }

    public function getClasses($ids)
    {
        return Classroom::query()->with('staff','major','enrollmentObject','area','period')
                                 ->whereIn('id', $ids)
                                 ->get();
    }

    public function getSemesterList($all_classes)
    {
        return Period::query()->whereIn('classroom_id', $all_classes)->get();
    }

}