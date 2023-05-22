<?php

namespace App\Http\Domain\Reports\Services\G820;

use App\Http\Domain\Reports\Repositories\G820\G820RepositoryInterface;
use App\Http\Enum\StudentStatus;
use App\Http\Domain\Reports\Services\XmlGenerator;
use App\Http\Enum\LockDay;
use App\Http\Enum\StudentReceivablePurpose;
use App\Http\Enum\StudentTypeWeek;
use Carbon\Carbon;

class ExecuteData
{
    /** @var \App\Http\Domain\Reports\Repositories\G820\G820Repository */
    private $g820_repository;
    
    public function __construct(G820RepositoryInterface $g820_repository)
    {
        $this->g820_repository = $g820_repository;
    }

    public function getAll($request,$type)
    {
        $searchData = $this->g820_repository->getAll($request);
        $data = $searchData['data'];
        $chosen_semester = $searchData['semester'];
        $students = $searchData['all_students'];
        $semester_list = $searchData['semester_list'];
        $student_status_changes = $searchData['student_status_changes'];
        $max_semester = 0;
        $rows =[];

        if(!empty($data))
        {
            $list_gdate_by_class        = [];
            $list_status_changes        = [];
            
            foreach($semester_list as $semes)
            {
                if($semes->semester == 1)
                {
                    $list_gdate_by_class[$semes->classroom_id]['g'.$semes->semester] = $semes->learn_began_date;
                } else {
                    $list_gdate_by_class[$semes->classroom_id]['g'.$semes->semester] = $semes->decision_date;
                }
                
                if(!isset($list_gdate_by_class[$semes->classroom_id]['max_semester']))
                {
                    $list_gdate_by_class[$semes->classroom_id]['max_semester'] = $semes->semester;
                } else {
                    $list_gdate_by_class[$semes->classroom_id]['max_semester'] = $list_gdate_by_class[$semes->classroom_id]['max_semester'] < $semes->semester ? $semes->semester : $list_gdate_by_class[$semes->classroom_id]['max_semester'];
                }
            }

            foreach($student_status_changes as $status_change)
            {
                $list_status_changes[$status_change['student_id']][] = $status_change;
            }

            $getThucThu = $this->g820_repository->getThucThu($data);
            $getThucThu = call_user_func_array('array_merge', array_map(function($thuc_thu) {
                return $thuc_thu;
            },$getThucThu));

            foreach ($data as $dat)
            {
                $history_in_classes = $dat->studentClassrooms->map(function($class) use($list_gdate_by_class) {
                    $class_g_dates = $list_gdate_by_class[$class->classroom_id];
                    unset($class_g_dates['max_semester']);
                    return [
                        'id' => $class->classroom_id,
                        'list_gdate' => $class_g_dates,
                        'semesters' => range(1,$list_gdate_by_class[$class->classroom_id]['max_semester']),
                        'began_at' => $class->began_at,
                        'ended_at' => $class->ended_at
                    ];
                })->toArray();
                
                $receivable = $dat->studentProfile->receivable->where('purpose',StudentReceivablePurpose::getValueByKeyVi('HOC_PHI'))->toArray();
                $actual_collected = $getThucThu[$dat->studentProfile->profile_code];
                $dat->semesters = [];
                $current_status = $dat->student_status;
                $student_status_changes = isset($list_status_changes[$dat->id]) ? $list_status_changes[$dat->id] : '';
                
                $semesters_detail = array_map(function($class) use ($actual_collected,$receivable,$dat,$student_status_changes,$current_status) {
                    $student_status_change = [];
                    $student_stts = [];
                    $current_status = $dat->student_status;

                    if(!empty($student_status_changes))
                    {
                        $student_status_change = $this->getStudentStatusChanges($student_status_changes,$class['list_gdate']);
                        foreach($student_status_change as $semester=>$status_change)
                        {
                            $semes = substr($semester,-1,1);
                            $student_stts["semester_$semes"] = (is_array($status_change) && isset($status_change['status'])) ? $status_change['status'] : StudentStatus::statusForReports($current_status);
                        }
                    }

                    $class_balance = array_map(function($semester) use ($actual_collected,$receivable,$current_status,$student_stts,$class){
                        $thuc_thu = 0;
                        if(isset($class['list_gdate']['g'.$semester]) && $class['began_at'] <= $class['list_gdate']['g'.$semester] && $class['ended_at'] != null && $class['ended_at'] < $class['list_gdate']['g'.$semester])
                        {
                            return [];
                        }

                        $this_receivable = array_filter(array_map(function($receivable) use($semester) {
                            if($receivable['learning_wave_number'] == $semester && $receivable['reference_table'] == 'classroom_receivables')
                            {
                                return $receivable['receivable'];
                            }
                            return [];
                        },$receivable));

                        $thuc_thu = isset($actual_collected['semester_'.$semester]) ? $actual_collected['semester_'.$semester] : 0;
                        foreach($actual_collected as $semes=>$paid)
                        {
                            if($semes == $semester)
                            {
                                $thuc_thu += $paid;
                            }
                        }

                        return  ["semester_$semester" => [
                            'semester'  => $semester,
                            'class'     => $class['id'],
                            'status'    => isset($student_stts["semester_$semester"]) ? $student_stts["semester_$semester"] : StudentStatus::statusForReports($current_status),
                            'phai_thu'  => !empty($this_receivable) ? (count($this_receivable) > 1 ? max(implode('',$this_receivable)) : implode('',$this_receivable)) : 0,
                            'thuc_thu'  => $thuc_thu != null ? round($thuc_thu,0) : 0
                        ]];
                        
                    },$class['semesters']);

                    return call_user_func_array('array_merge',array_map(function($balance) {
                        return $balance;
                    },$class_balance));
                },$history_in_classes);

                $semester_detail = call_user_func_array('array_merge',array_map(function($class_history) {
                    return $class_history;
                },$semesters_detail));
                $dat->semesters = $semester_detail;
                
                $list_semesters = array_values(array_map(function($semester){
                    return $semester['semester'];
                },$dat->semesters));

                $max_semester = max($list_semesters) > $max_semester ? max($list_semesters) : $max_semester;
                $previous_semester = max($list_semesters)-1;
                $dat->has_paid_previous_semester = 0;

                if((isset($dat->semesters["semester_$previous_semester"]) && $dat->semesters["semester_$previous_semester"]['thuc_thu'] - $dat->semesters["semester_$previous_semester"]['phai_thu'] >= 0) || max($list_semesters) == 1)
                {
                    $dat->has_paid_previous_semester = 1;
                } 

                if(substr($dat->getProfile->phone_number,0,1) != 0)
                {
                    $dat->getProfile->phone_number = '0'.$dat->getProfile->phone_number;
                }
                
                if($type == 'export')
                {
                    $documents = json_decode($dat->studentProfile->documents);
                    $decision_no = isset($documents->decision_no) ? $documents->decision_no : '';
                    $decision_date = isset($documents->decision_date) ? $documents->decision_date : '';
                    $rows[] = [
                        $dat->studentProfile->profile_code,
                        $dat->student_code,
                        $dat->getProfile->firstname,
                        $dat->getProfile->lastname,
                        $dat->getProfile->gender == 0 ? 'Nam' : 'Nữ',
                        $dat->getProfile->birthday ? date("d/m/Y",strtotime($dat->getProfile->birthday)) : $dat->getProfile->borned_year,
                        substr($dat->getProfile->phone_number,0,1) == 0 ? '\''.$dat->getProfile->phone_number : '\'0'.$dat->getProfile->phone_number,
                        $dat->getProfile->borned_place,
                        $dat->account,
                        $dat->email,
                        $dat->getProfile->address,
                        $dat->classrooms->first()->major?->name,
                        $dat->classrooms->first()->code,
                        $dat->classrooms->first()->enrollmentObject->shortcode,
                        $dat->classrooms->first()->area->code,
                        $decision_no,
                        $decision_date,
                        $dat->classrooms->first()->staff?->fullname,
                        array_key_exists('semester_1',$semester_detail) ? number_format($semester_detail['semester_1']['phai_thu'],0,'.',',') : '',
                        array_key_exists('semester_1',$semester_detail) ? number_format($semester_detail['semester_1']['thuc_thu'],0,'.',',') : '',
                        array_key_exists('semester_1',$semester_detail) ? number_format($semester_detail['semester_1']['thuc_thu'] - $semester_detail['semester_1']['phai_thu'],0,'.',',') : '',
                        in_array('semester_1',$dat->semesters) ? StudentStatus::statusForReports($dat->semesters['semester_1']['status']) : StudentStatus::statusForReports($current_status),
                        array_key_exists('semester_2',$semester_detail) ? number_format($semester_detail['semester_2']['phai_thu'],0,'.',',') : '',
                        array_key_exists('semester_2',$semester_detail) ? number_format($semester_detail['semester_2']['thuc_thu'],0,'.',',') : '',
                        array_key_exists('semester_2',$semester_detail) ? number_format($semester_detail['semester_2']['thuc_thu'] - $semester_detail['semester_2']['phai_thu'],0,'.',',') : '',
                        in_array('semester_2',$dat->semesters) ? StudentStatus::statusForReports($dat->semesters['semester_2']['status']) : (in_array(2,$list_semesters) ? StudentStatus::statusForReports($current_status) : ''),
                        array_key_exists('semester_3',$semester_detail) ? number_format($semester_detail['semester_3']['phai_thu'],0,'.',',') : '',
                        array_key_exists('semester_3',$semester_detail) ? number_format($semester_detail['semester_3']['thuc_thu'],0,'.',',') : '',
                        array_key_exists('semester_3',$semester_detail) ? number_format($semester_detail['semester_3']['thuc_thu'] - $semester_detail['semester_3']['phai_thu'],0,'.',',') : '',
                        in_array('semester_3',$dat->semesters) ? StudentStatus::statusForReports($dat->semesters['semester_3']['status']) : (in_array(3,$list_semesters) ? StudentStatus::statusForReports($current_status) : ''),
                        array_key_exists('semester_4',$semester_detail) ? number_format($semester_detail['semester_4']['phai_thu'],0,'.',',') : '',
                        array_key_exists('semester_4',$semester_detail) ? number_format($semester_detail['semester_4']['thuc_thu'],0,'.',',') : '',
                        array_key_exists('semester_4',$semester_detail) ? number_format($semester_detail['semester_4']['thuc_thu'] - $semester_detail['semester_4']['phai_thu'],0,'.',',') : '',
                        in_array('semester_4',$dat->semesters) ? StudentStatus::statusForReports($dat->semesters['semester_4']['status']) : (in_array(4,$list_semesters) ? StudentStatus::statusForReports($current_status) : ''),
                        array_key_exists('semester_5',$semester_detail) ? number_format($semester_detail['semester_5']['phai_thu'],0,'.',',') : '',
                        array_key_exists('semester_5',$semester_detail) ? number_format($semester_detail['semester_5']['thuc_thu'],0,'.',',') : '',
                        array_key_exists('semester_5',$semester_detail) ? number_format($semester_detail['semester_5']['thuc_thu'] - $semester_detail['semester_5']['phai_thu'],0,'.',',') : '',
                        in_array('semester_5',$dat->semesters) ? StudentStatus::statusForReports($dat->semesters['semester_5']['status']) : (in_array(5,$list_semesters) ? StudentStatus::statusForReports($current_status) : ''),
                        array_key_exists('semester_6',$semester_detail) ? number_format($semester_detail['semester_6']['phai_thu'],0,'.',',') : '',
                        array_key_exists('semester_6',$semester_detail) ? number_format($semester_detail['semester_6']['thuc_thu'],0,'.',',') : '',
                        array_key_exists('semester_6',$semester_detail) ? number_format($semester_detail['semester_6']['thuc_thu'] - $semester_detail['semester_6']['phai_thu'],0,'.',',') : '',
                        in_array('semester_6',$dat->semesters) ? StudentStatus::statusForReports($dat->semesters['semester_6']['status']) : (in_array(6,$list_semesters) ? StudentStatus::statusForReports($current_status) : ''),
                        array_key_exists('semester_7',$semester_detail) ? number_format($semester_detail['semester_7']['phai_thu'],0,'.',',') : '',
                        array_key_exists('semester_7',$semester_detail) ? number_format($semester_detail['semester_7']['thuc_thu'],0,'.',',') : '',
                        array_key_exists('semester_7',$semester_detail) ? number_format($semester_detail['semester_7']['thuc_thu'] - $semester_detail['semester_7']['phai_thu'],0,'.',',') : '',
                        in_array('semester_7',$dat->semesters) ? StudentStatus::statusForReports($dat->semesters['semester_7']['status']) : (in_array(7,$list_semesters) ? StudentStatus::statusForReports($current_status) : ''),
                        array_key_exists('semester_8',$semester_detail) ? number_format($semester_detail['semester_8']['phai_thu'],0,'.',',') : '',
                        array_key_exists('semester_8',$semester_detail) ? number_format($semester_detail['semester_8']['thuc_thu'],0,'.',',') : '',
                        array_key_exists('semester_8',$semester_detail) ? number_format($semester_detail['semester_8']['thuc_thu'] - $semester_detail['semester_8']['phai_thu'],0,'.',',') : '',
                        in_array('semester_8',$dat->semesters) ? StudentStatus::statusForReports($dat->semesters['semester_8']['status']) : (in_array(8,$list_semesters) ? StudentStatus::statusForReports($current_status) : ''),
                        array_key_exists('semester_9',$semester_detail) ? number_format($semester_detail['semester_9']['phai_thu'],0,'.',',') : '',
                        array_key_exists('semester_9',$semester_detail) ? number_format($semester_detail['semester_9']['thuc_thu'],0,'.',',') : '',
                        array_key_exists('semester_9',$semester_detail) ? number_format($semester_detail['semester_9']['thuc_thu'] - $semester_detail['semester_9']['phai_thu'],0,'.',',') : '',
                        in_array('semester_9',$dat->semesters) ? StudentStatus::statusForReports($dat->semesters['semester_9']['status']) : (in_array(9,$list_semesters) ? StudentStatus::statusForReports($current_status) : ''),
                    ];
                }
            }
        }
        
        if($type == 'index')
        {
            return [ 
                'data' => $data, 
                'semester' => $max_semester 
            ];
        } else {
            return [ $rows, $chosen_semester, $max_semester, $data ];
        }
    }

    public function dataForG820B($students,$semester)
    {
        $all_classroom_ids = array_unique($students->map(function($student) {
            return $student->classrooms->first()->id;
        })->toArray());
        $classes = $this->g820_repository->getClasses($all_classroom_ids);
        $student_type_a = StudentTypeWeek::qualifiedStudents();
        $rows = [];
        $total_students = 0;
        $unpaid_students = 0;
        $ty_le = 0;
        $all_status_count = [
            '00_XOA_QUYET_DINH_SINH_VIEN'       => 0,
            '03_NGHI_HOC'                       => 0,
            '04_BAO_LUU'                        => 0,
            '05_TAM_NGUNG_HOC_DO_CHUA_HS_HP'    => 0,
            '06_DANG_HOC_CHUA_HS'               => 0,
            '07_DANG_HOC_CHO_QĐNH'              => 0,
            '08_DANG_HOC_DA_CO_QĐNH'            => 0,
            '09_DA_TOT_NGHIEP'                  => 0,
        ];

        foreach($classes as $classroom)
        {
            $in_debt = 0;
            $all_students_in_class = 0;
            $class = [
                '00_XOA_QUYET_DINH_SINH_VIEN'       => 0,
                '03_NGHI_HOC'                       => 0,
                '04_BAO_LUU'                        => 0,
                '05_TAM_NGUNG_HOC_DO_CHUA_HS_HP'    => 0,
                '06_DANG_HOC_CHUA_HS'               => 0,
                '07_DANG_HOC_CHO_QĐNH'              => 0,
                '08_DANG_HOC_DA_CO_QĐNH'            => 0,
                '09_DA_TOT_NGHIEP'                  => 0,
            ];

            $max_semester = $semester != '' ? $semester : max($classroom->period->pluck('semester')->toArray());

            foreach($students as $student)
            {
                $status_in_class = array_filter(array_map(function ($semester) use ($max_semester,$classroom) {
                    if($semester['semester'] === $max_semester && $semester['class'] === $classroom->id)
                    {
                        return $semester['status'];
                    }
                    return [];
                },$student->semesters));

                if(empty($status_in_class))
                    continue;
                
                if(in_array($student->learningEngagement->student_type_fourth_week,$student_type_a))
                {
                    $all_students_in_class++;
                    $class[$status_in_class['semester_'.$max_semester]]++;
                    $all_status_count[$status_in_class['semester_'.$max_semester]]++;
                    if($student->has_paid_previous_semester == 0)
                    {
                        $in_debt++;
                    }
                }
            }

            $g_date = $classroom->period->where('semester',$max_semester)->first() != null ? $classroom->period->where('semester',$max_semester)->first()->decision_date : '';
            $rows[] = [
                $classroom->staff?->fullname ?? '',
                $classroom->major?->name ?? '',
                $classroom->enrollmentObject?->shortcode ?? '',
                $classroom->code,
                $classroom->area?->code ?? '',
                $max_semester,
                $g_date != '' ? date('d/m/Y',strtotime($g_date)) : '',
                $all_students_in_class,
                $class['00_XOA_QUYET_DINH_SINH_VIEN'] != 0 ? $class['00_XOA_QUYET_DINH_SINH_VIEN'] : "0",
                $class['03_NGHI_HOC'] != 0 ? $class['03_NGHI_HOC'] : "0",
                $class['04_BAO_LUU'] != 0 ? $class['04_BAO_LUU'] : "0",
                $class['05_TAM_NGUNG_HOC_DO_CHUA_HS_HP'] != 0 ? $class['05_TAM_NGUNG_HOC_DO_CHUA_HS_HP'] : "0",
                $class['06_DANG_HOC_CHUA_HS'] != 0 ? $class['06_DANG_HOC_CHUA_HS'] : "0",
                $class['07_DANG_HOC_CHO_QĐNH'] != 0 ? $class['07_DANG_HOC_CHO_QĐNH'] : "0",
                $class['08_DANG_HOC_DA_CO_QĐNH'] != 0 ? $class['08_DANG_HOC_DA_CO_QĐNH'] : "0",
                $class['09_DA_TOT_NGHIEP'] != 0 ? $class['09_DA_TOT_NGHIEP'] : "0",
                $in_debt != 0 ? $in_debt : "0",
            ];
            $total_students += $all_students_in_class;
            $unpaid_students += $in_debt;
        }

        $sum = [
            'total_students' => $total_students,
            'all_status_count' => $all_status_count,
            'unpaid_students' => $unpaid_students,
        ];
        // dd($rows);
        return [ $rows,$sum ];
    }

    /*
    * Get student status logs.
    */
    public function getStudentStatusChanges($list_status_changes,$class_g_date) :array
    {
        $status_changes = [];
        $available_semesters = array_map(function($key) {
            return (int)substr($key,1);
        },array_keys($class_g_date));

        if(count($class_g_date) > 1)
        {
            $max_semester = max($available_semesters);
            for($i = 1; $i <= $max_semester;$i++)
            {
                if(isset($class_g_date["g".$i+1]))
                {
                    ${"g".$i + 1} = date_create($class_g_date["g".$i + 1]);

                    // Get student status of each semesters depends on when the change has been made

                    foreach($list_status_changes as $status_change)
                    {
                        // If this is  the first semester...
                        if($i == 1 && isset($class_g_date["g".$i]) && (!isset($status_changes["semester_".$i]) || $status_changes["semester_".$i]['date'] > date('Y-m-d H:i:s',strtotime($status_change['began_at']))))
                        {
                            $status_changes["semester_".$i]['date'] = $status_change['began_at'];
                            $status_changes["semester_".$i]['status'] = isset($status_change['value']) ? StudentStatus::statusForReports($status_change['value']) : StudentStatus::statusForReports(StudentStatus::DANG_HOC_CHO_QDNH);
                        }

                        // If status log's began date is earlier than the G date(at least 1 seconds earlier), and the ended date is null or greater than the G date...
                        if(date('Y-m-d',strtotime($status_change['began_at'])) <= date_sub(${"g".$i + 1},date_interval_create_from_date_string("1 second"))
                            && (date('Y-m-d',strtotime($status_change['ended_at'])) == null || date_create($status_change['ended_at']) > date_sub(${"g".$i + 1},date_interval_create_from_date_string("1 second"))))
                        {
                            //if the record of this semester has already been added, and the began date of that record is earlier than the began date of this status log...
                            if(!isset($status_changes["semester_".$i + 1]) ||
                                $status_changes["semester_".$i + 1]['date'] <= date('Y-m-d H:i:s',strtotime($status_change['began_at'])))
                            {
                                $status_changes["semester_".$i + 1]['date'] = $status_change['began_at'];
                                $status_changes["semester_".$i + 1]['status'] = StudentStatus::statusForReports($status_change['value']);
                            }
                        }
                    }
                }
            }
            if(!empty($status_changes))
            {
                for($i = 1; $i <= $max_semester ; $i++)
                {
                    if($i == 1 && !isset($status_changes["semester_".$i]))
                    {
                        $status_changes["semester_1"]['status'] = '';
                    }

                    if($i + 1 <= $max_semester && (!isset($status_changes["semester_".$i + 1]) || $status_changes["semester_".$i + 1] == ''))
                    {
                        $status_changes["semester_".$i + 1]['status'] = $status_changes["semester_".$i]['status'];
                    } 
                }
            }
        }
        return $status_changes;
    }
}