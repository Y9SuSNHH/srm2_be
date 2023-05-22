<?php

namespace App\Http\Domain\Reports\Repositories\G120;

use App\Eloquent\Student;
use App\Eloquent\Classroom;
use App\Eloquent\Staff;
use App\Eloquent\Crm\SinhVien;
use App\Eloquent\Crm\Student as CrmStudent;
use App\Http\Domain\Reports\Services\G120\ManageEngagementProcessesService;
use App\Http\Enum\ProfileStatus;
use App\Http\Enum\StudentReceivablePurpose;
use App\Http\Enum\TuitionFee;
use App\Http\Enum\StudentTypeWeek;
use App\Http\Enum\Level;
use Illuminate\Support\Facades\DB;

class G120Repository implements G120RepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll($request) 
    {
        try {
            $students = $this->g120QueryStatement($request)
                             ->get()
                             ->transform(function($student) {
                                return [
                                    'id'                            => $student->id,
                                    'area'                          => $student->classrooms->first()?->area?->code,
                                    'profile_code'                  => $student->studentProfile->profile_code,
                                    'student_code'                  => $student->student_code,
                                    'firstname'                     => $student->studentProfile->profile->firstname,
                                    'lastname'                      => $student->studentProfile->profile->lastname,
                                    'gender'                        => $student->studentProfile->profile->gender === 0 ? 'Nam' : 'Nữ',
                                    'borned_place'                  => $student->studentProfile->profile->borned_place,
                                    'class'                         => $student->classrooms->first()?->code,
                                    'major'                         => $student->classrooms->first()?->major?->shortcode,
                                    'first_semester_receivable'     => !($student->studentProfile->receivable->isEmpty()) ? $student->studentProfile->receivable->first()->receivable : 0,
                                    'first_semester_revenue'        => 0,
                                    'profile_status'                => ($student->profile_status != null && $student->profile_status != '') ? ProfileStatus::search($student->profile_status) : '',
                                    'is_join_first_day_of_school'   => $student->learningEngagement?->is_join_first_day_of_school,
                                    'is_join_first_week'            => $student->learningEngagement?->is_join_first_week,
                                    'is_join_fourth_week'           => $student->learningEngagement?->is_join_fourth_week,
                                    'study_later'                   => $student->learningEngagement?->student_type_first_week === 'B3' ? 1 : ($student->learningEngagement?->student_type_fourth_week === 'B3' ? 1 : 0),
                                    'student_type_first_week'       => !empty($student->learningEngagement) ? StudentTypeWeek::search($student->learningEngagement?->student_type_first_week) : '',
                                    'student_type_fourth_week'      => !empty($student->learningEngagement) ? StudentTypeWeek::search($student->learningEngagement?->student_type_fourth_week) : '',
                                    'note'                          => $student->note
                                ];
                        });
            return $students;
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'line'   => $e->getLine(),
                'file'    => $e->getFile()
            ];
        }
    }
    
    public function getActualCollected($student_ids)
    {
        $students = $this->g120QueryStatement(false)->whereIn('id', $student_ids)->get();
        $profile_codes = $students->map(function ($student) {
            /** @var EloquentStudent $student */
            return ['id' => $student->id, 'code' => $student->studentProfile->profile_code];
        })->pluck('code', 'id')->toArray();

        $query = CrmStudent::query()->getModel();
        $thuc_thu = $query->with(['amountsReceived' => function($query) {
            /** @var Builder $query */
            $query->where('muc_dich_thu', TuitionFee::FREE)
                ->where(function ($query) {
                    /** @var Builder $query */
                    $query->orWhere('deleted', 0);
                    $query->orWhereNull('deleted');
                })
                ->where('dot_hoc_so', 1);
        }])
            ->whereIn('ma_ho_so', $profile_codes)
            ->get()->transform(function ($sv) {
                /** @var CrmStudent $sv */
                $thuc_thu = $sv->amountsReceived->sum('thuc_nop');
                return [
                    'code' => $sv->ma_ho_so,
                    'paid' => $thuc_thu,
                ];
            })->pluck('paid', 'code')->toArray();
        return [$students,$thuc_thu];
    }

    
    public function getStudentsForExport($request)
    {
        $students = $this->g120QueryStatement($request)
                         ->get()
                         ->transform(function($student) {
                             return [
                                 'id'                            => $student->id,  
                                 'area'                          => $student->classrooms->first()->area->code,
                                 'profile_code'                  => $student->studentProfile->profile_code,
                                 'student_code'                  => $student->student_code,
                                 'firstname'                     => $student->studentProfile->profile->firstname,
                                 'lastname'                      => $student->studentProfile->profile->lastname,
                                 'birthday'                      => date('d/m/Y',strtotime($student->studentProfile->profile->birthday)),
                                 'gender'                        => $student->studentProfile->profile->gender === 1 ? 'Nam' : 'Nữ',
                                 'borned_place'                  => $student->studentProfile->profile->borned_place,
                                 'phone_number'                  => $student->studentProfile->profile->phone_number,
                                 'personal_email'                => $student->studentProfile->profile->email,
                                 'address'                       => $student->studentProfile->profile->address,
                                 'account'                       => $student->account,
                                 'class'                         => $student->classrooms->first()->code,
                                 'major'                         => $student->classrooms->first()->major?->shortcode,
                                 'admissions_counselor'          => $student->studentProfile->getAdmissionsCounselor?->fullname,
                                 'learning_manager'              => $student->classrooms->first()->staff?->fullname,
                                 'first_semester_receivable'     => !($student->studentProfile->receivable->isEmpty()) ? $student->studentProfile->receivable->first()->receivable : 0,
                                 'first_semester_revenue'        => 0,
                                 'differencial'                  => 0,
                                 'profile_status'                => ProfileStatus::search($student->profile_status),
                                 'level'                         => '',
                                 'is_join_first_day_of_school'   => $student->learningEngagement?->is_join_first_day_of_school,
                                 'is_join_first_week'            => $student->learningEngagement?->is_join_first_week,
                                 'is_join_fourth_week'           => $student->learningEngagement?->is_join_fourth_week,
                                 'student_type_first_week'       => StudentTypeWeek::search($student->learningEngagement?->student_type_first_week),
                                 'student_type_third_week'       => '',
                                 'student_type_fourth_week'      => StudentTypeWeek::search($student->learningEngagement?->student_type_fourth_week),
                                 'note'                          => $student->note
                             ];
                         });

        return $students;
    }

    public function getG120ByClass($request)
    {
        $query = Classroom::query()->with(['students' => function($q) {
            $q->with(['studentProfile' => function($q) {
                $q->with('profile');
            },'learningEngagement']);      
        },'staff','major','area'])
        ->when(isset($request['first_day_of_school']) && $request['first_day_of_school'] != '' && $request['first_day_of_school'] != 'all' ,function($q) use ($request) {
            $q->whereHas('enrollmentWave',function($q) use ($request) {
                $q->where('first_day_of_school',$request['first_day_of_school']);
            });
        })
        ->when(isset($request['area']) && $request['area'] != '' && $request['area'] != 'all' ,function($q) use ($request) {
            $q->whereHas('area',function($q) use ($request) {
                $q->where('areas.id',$request['area']);
            });
        })
        ->when(isset($request['major']) && $request['major'] != '' && $request['major'] != 'all' ,function($q) use ($request) {
            $q->where('major_id',$request['major']);
        })
        ->when(isset($request['staff']) && $request['staff'] != '' && $request['staff'] != 'all' ,function($q) use ($request) {
            $q->whereHas('staff',function($q) use ($request) {
                $q->where('staffs.id',$request['staff']);
            });
        })
        ->when(isset($request['classes']) && $request['classes'] != '' && $request['classes'] != 'all' ,function($q) use ($request) {
            $q->whereIn('id',$request['classes']);
        })
        ->when(isset($request['keywords']) && $request['keywords'] != '' && $request['keywords'] != 'all' ,function($q) use ($request) {
            // dd($request['keywords']);
            $q->whereHas('students',function($q) use ($request) {
                $q->whereRelation('studentProfile.profile',DB::raw("lower(CONCAT(profiles.firstname,' ',profiles.lastname))"), 'ilike', "%{$request['keywords']}%")
                  ->orWhere('student_code','like',$request['keywords'])
                  ->orWhereRelation('studentProfile','profile_code','LIKE',$request['keywords']);
            });
        })
        ->orderBy('id')
        ->get()
        ->transform(function($class) {
            if($class->students->isEmpty())
                return [];
            $l8 = count($class->students->filter(function($item) {
                return $item->studentProfile?->level === Level::L8;
            }));
            $l5b = count($class->students->filter(function($item) {
                return $item->studentProfile?->level === Level::L5B;
            }));
            $total = count($class->students);
            return [
                'area'              => $class->area?->code,
                'staff'             => $class->staff?->fullname,
                'major'             => $class->major?->shortcode,
                'class_name'        => $class->code,
                'total_students'    => $total,
                'l8'                => $l8,
                'A1'                => count($class->students->filter(function($item) {
                                            return $item->learningEngagement?->student_type_fourth_week === StudentTypeWeek::A1;
                                        })),
                'A2'                => count($class->students->filter(function($item) {
                                            return $item->learningEngagement?->student_type_fourth_week === StudentTypeWeek::A2;
                                        })),
                'B1'                => count($class->students->filter(function($item) {
                                            return $item->learningEngagement?->student_type_fourth_week === StudentTypeWeek::B1;
                                        })),
                'B2'                => count($class->students->filter(function($item) {
                                            return str_contains(StudentTypeWeek::search($item->learningEngagement?->student_type_fourth_week),'B2');
                                        })),
                'B3'                => count($class->students->filter(function($item) {
                                            return $item->learningEngagement?->student_type_fourth_week === StudentTypeWeek::B3;
                                        })),
                'C'                 => count($class->students->filter(function($item) {
                                            return $item->learningEngagement?->student_type_fourth_week === StudentTypeWeek::C;
                                        })),
                'l8/l5b'            => $l5b != 0 ? round($l8/$l5b,2) : $l8,
                'percent'           => round($l8/$total*100,2),
            ];
        });
        
        return array_filter($query->toArray());
    }

    public function getStaff($id)
    {
        return Staff::query()->where('id', $id)
                             ->where('team',3)
                             ->first();
    }

    private function g120QueryStatement($conditions) {
        $query =  Student::query()->with(['studentProfile' => function($q) {
                                        $q->with(['profile','area','major','getAdmissionsCounselor','receivable' => function($q) {
                                            $q->where('learning_wave_number',1)->where('purpose',StudentReceivablePurpose::getValueByKeyVi('HOC_PHI'));
                                        }]);
                                    },'classrooms' => function($q) {
                                        $q->with('enrollmentWave','staff','major');
                                    },'learningEngagement']);
        if($conditions)
        {
            $query->when(isset($conditions['first_day_of_school']) && $conditions['first_day_of_school'] != '' && $conditions['first_day_of_school'] != 'all' ,function($q) use ($conditions) {
                                $q->whereHas('classrooms.enrollmentWave',function($q) use ($conditions) {
                                    $q->where('first_day_of_school',$conditions['first_day_of_school']);
                                });
                             })
                ->when(isset($conditions['area']) && $conditions['area'] != '' && $conditions['area'] != 'all' ,function($q) use ($conditions) {
                    $q->whereHas('classrooms',function($q) use ($conditions) {
                        $q->where('area_id',$conditions['area']);
                    });
                })
                ->when(isset($conditions['major']) && $conditions['major'] != '' && $conditions['major'] != 'all' ,function($q) use ($conditions) {
                    $q->whereHas('classrooms',function($q) use ($conditions) {
                        $q->where('major_id',$conditions['major']);
                    });
                })
                ->when(isset($conditions['staff']) && $conditions['staff'] != '' && $conditions['staff'] != 'all' ,function($q) use ($conditions) {
                    $q->whereHas('classrooms',function($q) use ($conditions) {
                        $q->where('staff_id',$conditions['staff']);
                    });
                })
                ->when(isset($conditions['classes']) && $conditions['classes'] != '' && $conditions['classes'] != 'all' ,function($q) use ($conditions) {
                    $q->whereHas('classrooms',function($q) use ($conditions) {
                        $q->whereIn('classrooms.id',$conditions['classes']);
                    });
                })
                ->when(isset($conditions['keywords']) && $conditions['keywords'] != '' && $conditions['keywords'] != 'all' ,function($q) use ($conditions) {
                    $q->whereRelation('studentProfile.profile',DB::raw("lower(CONCAT(profiles.firstname,' ',profiles.lastname))"), 'ilike', "%{$conditions['keywords']}%")
                        ->orWhere('student_code','like',$conditions['keywords'])
                        ->orWhereRelation('studentProfile','profile_code','LIKE',$conditions['keywords']);
                })
                ->orderByRaw('(select p.id from majors AS p inner join classrooms sp on sp.major_id = p.id inner join student_classrooms sc on sc.classroom_id = sp.id where sc.student_id = students.id)')
                ->orderByRaw('(select cl.id from classrooms cl inner join student_classrooms sc on sc.classroom_id = cl.id where sc.student_id = students.id)')
                ->orderByRaw('(select p.lastname from profiles AS p inner join student_profiles sp on sp.profile_id = p.id where sp.id = students.student_profile_id)');
        }

        return $query;
    }

    public function executeUpdate($sql) {
        return DB::statement($sql);
    }
}