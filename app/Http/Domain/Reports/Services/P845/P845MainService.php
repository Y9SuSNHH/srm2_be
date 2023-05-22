<?php

namespace App\Http\Domain\Reports\Services\P845;

use App\Http\Domain\Reports\Repositories\P845\P845RepositoryInterface;
use App\Http\Enum\SemesterKPI;
use Carbon\Carbon;

class P845MainService
{
    /** @var \App\Http\Domain\Reports\Repositories\P845\P845Repository */
    private $p845_repository;
    
    public function __construct(P845RepositoryInterface $p845_repository)
    {
        $this->p845_repository = $p845_repository;
    }

    public function getAll($request,$type) {
        [ $records,$chosen_semester ] = $this->p845_repository->getAll($request,$type);
        $sheet_1_rows = [];
        $sheet_2_rows = [];
        $sheet_3_rows = [];
        if($type == 'index')
        {
            $search_sheet = $request['chosen_report'] === 'A' ? 1 : ($request['chosen_report'] === 'B' ? 2 : ($request['chosen_report'] === 'C' ? 3 : 0));
            if($search_sheet == 0)
                return 'Hãy chọn báo cáo muốn hiển thị';
        }

        $today = Carbon::now();
        $price = 370000;
        $data_chunks = $records->chunk(100);
        foreach($data_chunks as $data_index=>$data)
        {
            foreach($data as $index=>$dat)
            {
                // Get collect semester list
                $collect_semester_list = !($dat->period->isEmpty()) ? array_filter(array_map(function($record) use ($chosen_semester,$dat) {
                    if($chosen_semester != '')
                    {
                        return ($chosen_semester == $record['semester']) ? $record['semester'] : [];
                    }
                    return $record['semester'];
                }, $dat->period->toArray())) : [];
    
                // If that one's exists...
                if(!empty($collect_semester_list))
                {
                    $allActualCollects = $this->p845_repository->getThucThu($dat->students);
    
                    foreach($collect_semester_list as $collect_semester)
                    {
                        $has_count_revenue = [];
                        $has_sheet_1 = 0;
                        $has_sheet_2 = 0;
                        $has_sheet_3 = 0;
                        $special_case = $dat->major->shortcode === 'NNA' ? (in_array($collect_semester,[2,3]) ? true : false) : false;
    
                        if($collect_semester == 1)
                        {
                            continue;
                        }
                        
                        $collect_began_date_arr = $dat->period->map(function ($period) use ($collect_semester){
                            if($period['semester'] == $collect_semester)
                            {
                                return $period['collect_began_date'];
                            }
                            return [];
                        })->toArray();
                        
                        $collect_began_date_arr = array_filter($collect_began_date_arr);
                        $collect_began_date = reset($collect_began_date_arr);
                        $first_day_of_collect_period = '';
                        $last_day_of_collect_period = '';
                        $total_receivables_count = 0;
                        $list_weeks = [];
    
                        // Get list of weeks with first and last day of them
                        for($i = 1; $i <= 8; $i++)
                        {
                            if($i == 1 )
                            {
                                $first_day_of_the_week = $first_day_of_collect_period = clone $collect_began_date;
                            } else {
                                $first_day_of_the_week = clone $collect_began_date->addDay();
                            }
                                
                            $end_of_the_week = clone $collect_began_date->addDays(6);
    
                            if($i == 8)
                            {
                                $last_day_of_collect_period = $end_of_the_week;
                            }
    
                            $list_weeks['tuan_'.$i] = [
                                'first_day' => $first_day_of_the_week,
                                'last_day'  => $end_of_the_week,
                                'number_of_revenue' => 0,
                                'revenue'   => 0
                            ];
                        }
    
                        $sheet_1_range = [
                            'first_day' => $list_weeks['tuan_1']['first_day'],
                            'last_day'  => $list_weeks['tuan_6']['last_day'],
                        ];
    
                        $sheet_2_range = [
                            'first_day' => $list_weeks['tuan_7']['first_day'],
                            'last_day' => $list_weeks['tuan_8']['last_day'],
                        ];
    
                        $sheet_1_detail = $sheet_2_detail = $this->weekTotalData();
    
                        $receivable = 0; // All receivables
                        
                        // $out_of_collect_time: Storage for revenues which students paid their fees out of deadline
                        // $early_paid: Storage for revenues which in any cases these students paid their fees before the collect period starts
                        $out_of_collect_time = $early_paid = $this->weekTotalData();
    
                        // If today is not between each sheet's date range...
                        for($i = 1; $i <= 2;$i++)
                        {
                            if($today->between(${'sheet_'.$i.'_range'}['first_day'], ${'sheet_'.$i.'_range'}['last_day']))
                            {
                                ${'has_sheet_'.$i} = 1;
                            }
                        }
                        
                        if($today->gt($last_day_of_collect_period))
                        {
                            $has_sheet_3 = 1;
                            $has_sheet_2 = 0;
                            $has_sheet_1 = 0;
                        }
                        
                        if(isset($search_sheet) && ${'has_sheet_'.$search_sheet} != 1)
                        {
                            continue;
                        }
                        
                        // Start getting revenues based on the receipt date
                        if(!($dat->students->isEmpty()))
                        {
                            foreach($dat->students as $student)
                            {
                                // Get all revenue's records
                                $actual_collects = isset($allActualCollects[$student->studentProfile->profile_code]['semester_'.$collect_semester]) ? $allActualCollects[$student->studentProfile->profile_code]['semester_'.$collect_semester] : [];
                                $student_receivable = 0;
                                // Add to receivable if the record is belong to this semester
                                if(!($student->getStudentReceivables->isEmpty()))
                                {
                                    foreach($student->getStudentReceivables as $receivable_record)
                                    {
                                        if($receivable_record->learning_wave_number == $collect_semester)
                                        {
                                            $receivable += $receivable_record->receivable;
                                            $student_receivable = $receivable_record->receivable;
                                            $total_receivables_count++;
                                        }
                                            
                                    }
                                    
                                }
                                
                                if(!empty($actual_collects))
                                {
                                    // Get revenue if receipt date is between collect date range
                                    foreach($actual_collects as $thuc_thu)
                                    {
                                        if($has_sheet_3 != 1)
                                        {
                                            if($thuc_thu['receipt_date']->lt($first_day_of_collect_period))
                                            {
                                                $early_paid['total_revenue'] += $thuc_thu['paid'];
                                                if(!array_key_exists($student->studentProfile->profile_code,$has_count_revenue))
                                                {
                                                    $has_count_revenue[$student->studentProfile->profile_code] = 0;
                                                }
    
                                                $has_count_revenue[$student->studentProfile->profile_code] += $thuc_thu['paid'];
                                                
                                                $this->isCount($student->studentProfile->profile_code,$student_receivable,$thuc_thu,$has_count_revenue) 
                                                ? $early_paid['number_of_revenue']++ 
                                                : $early_paid['number_of_revenue']+0;
                                            }
    
                                            foreach($list_weeks as $week_name=>$week)
                                            {
                                                if($thuc_thu['receipt_date']->between($week['first_day'],$week['last_day']))
                                                {
                                                    if(!array_key_exists($student->studentProfile->profile_code,$has_count_revenue))
                                                    {
                                                        $has_count_revenue[$student->studentProfile->profile_code] = 0;
                                                    }
                                                    $list_weeks[$week_name]['revenue'] += $thuc_thu['paid'];
                                                    $has_count_revenue[$student->studentProfile->profile_code] += $thuc_thu['paid'];
    
                                                    $this->isCount($student->studentProfile->profile_code,$student_receivable,$thuc_thu,$has_count_revenue) 
                                                    ? $list_weeks[$week_name]['number_of_revenue']++ 
                                                    : $list_weeks[$week_name]['number_of_revenue']+0;
    
                                                    if(in_array($week_name,SemesterKPI::P845AWeeks()))
                                                    {
                                                        $sheet_1_detail['total_revenue'] += $thuc_thu['paid'];
                                                        
                                                        $this->isCount($student->studentProfile->profile_code,$student_receivable,$thuc_thu,$has_count_revenue) 
                                                        ? $sheet_1_detail['number_of_revenue']++ 
                                                        : $sheet_1_detail['number_of_revenue']+0;
                                                    } else if(in_array($week_name,['tuan_7', 'tuan_8'])) {
                                                        $sheet_2_detail['total_revenue'] += $thuc_thu['paid'];
                                                        
                                                        $this->isCount($student->studentProfile->profile_code,$student_receivable,$thuc_thu,$has_count_revenue) 
                                                        ? $sheet_2_detail['number_of_revenue']++ 
                                                        : $sheet_2_detail['number_of_revenue']+0;
                                                    }
    
                                                }
                                            }
                                        } else {
                                            // If the revenue record is out of collect date range, we'll put it in P845C
                                            if(!isset($thuc_thu['paid']))
                                            {
                                                dd($thuc_thu);
                                            }
                                            
                                            $out_of_collect_time['total_revenue'] += $thuc_thu['paid'];
                                            
                                            $this->isCount($student->studentProfile->profile_code,$student_receivable,$thuc_thu,$has_count_revenue) 
                                            ? $out_of_collect_time['number_of_revenue']++ 
                                            : $out_of_collect_time['number_of_revenue']+0;
    
                                            if(!array_key_exists($student->studentProfile->profile_code,$has_count_revenue))
                                            {
                                                $has_count_revenue[$student->studentProfile->profile_code] = 0;
                                            }
    
                                            $has_count_revenue[$student->studentProfile->profile_code] += $thuc_thu['paid'];
                                        }
                                    }
                                }
                                   
                            }
                            
                            $class_period = !($dat->period->isEmpty()) ? $dat->period->where('semester',$collect_semester)->first() : '';
                            $receivables_kpi = round($total_receivables_count*SemesterKPI::getValueByKey('KY_'.$collect_semester.($special_case ? '_NNA' : ''))['tong']/100,0);
                            
                            if($has_sheet_1 == 1)
                            {
                                $has_sheet_2 = 0;
                                $has_sheet_3 = 0;
                                $sheet_1_rows[] = [
                                    !empty($dat->staff) ? $dat->staff->fullname : '', // QLHT
                                    $dat->code, // Lớp quản lý
                                    $dat->major->shortcode, // Ngành
                                    $dat->enrollmentObject->shortcode, // Đối tượng
                                    $collect_semester, // Kỳ thu
                                    'Đang thu', // Trạng thái thu
                                    !empty($class_period) ? $class_period->learn_began_date->format('d/m/Y') : '', // Ngày bắt đầu học
                                    !empty($class_period) ? $class_period->collect_began_date->format('d/m/Y') : '', // Ngày bắt đầu thu
                                    !empty($class_period) ? $class_period->collect_ended_date->format('d/m/Y') : '', // Deadline ngày thu
                                    count($dat->students), // Số lượng SV đang học
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? SemesterKPI::getValueByKey('KY_'.$collect_semester.($special_case ? '_NNA' : ''))['tong'] .'%' : '') : '', // Mục tiêu kỳ
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $total_receivables_count : '') : '', // Số lượng phải thu
                                    0, // Tín chỉ
                                    number_format($price,-1,'.',',').' đ', // Đơn giá
                                    number_format(round($receivable,2),-1,'.',',') . ' đ', // Doanh thu dự kiến theo kế hoạch
                                    $receivables_kpi, // Số lượng phải thu theo mục tiêu
                                    number_format($sheet_1_detail['number_of_revenue'],-1,'.',','), // Số món thu tích lũy
                                    $sheet_1_detail['number_of_revenue'] - $receivables_kpi != 0 ? $sheet_1_detail['number_of_revenue'] - $receivables_kpi : '0',// Chênh lệch số món với mục tiêu
                                    $collect_semester != '' ? number_format($sheet_1_detail['total_revenue'],-1,'.',',') : 0, // Số tiền thu tích lũy
                                    
                                    // Nộp sớm
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $early_paid['number_of_revenue'] : 0) : 0, // Số món thực tế đã thu trong tuần
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? number_format($early_paid['total_revenue'],-1,'.',',') : 0) : 0, // Số thực tế đã thu trong tuần
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? round($early_paid['number_of_revenue']/($total_receivables_count != 0 ? $total_receivables_count*SemesterKPI::getValueByKey('KY_'.$collect_semester)['tong']*0.01 : 1)*100 ,2).' %' : 0) : 0, // % thực tế đạt được trong tuần
    
                                    // Tuần 1
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? SemesterKPI::getValueByKey('KY_'.$collect_semester)['tuan_1'] .'%' : '') : '', // Mục tiêu(Tuần 1)
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $list_weeks['tuan_1']['number_of_revenue'] : 0) : 0, // Số món thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_1',$list_weeks)) ? number_format($list_weeks['tuan_1']['revenue'],-1,'.',',') : 0) : 0, // Số thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_1',$list_weeks)) ? round($list_weeks['tuan_1']['number_of_revenue']/($total_receivables_count != 0 ? $total_receivables_count*SemesterKPI::getValueByKey('KY_'.$collect_semester)['tong']*0.01 : 1)*100 ,2).' %' : 0) : 0, // % thực tế đạt được trong tuần
                                    
                                    // Tuần 2
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? SemesterKPI::getValueByKey('KY_'.$collect_semester.($special_case ? '_NNA' : ''))['tuan_2'] .'%' : '') : '', // Mục tiêu(Tuần 2)
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $list_weeks['tuan_2']['number_of_revenue'] : 0) : 0, // Số món thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_2',$list_weeks)) ? number_format($list_weeks['tuan_2']['revenue'],-1,'.',',') : 0) : 0, // Số thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_2',$list_weeks)) ? round($list_weeks['tuan_2']['number_of_revenue']/($total_receivables_count != 0 ? $total_receivables_count*SemesterKPI::getValueByKey('KY_'.$collect_semester.($special_case ? '_NNA' : ''))['tong']*0.01 : 1)*100 ,2).' %' : 0) : 0, // % thực tế đạt được trong tuần
                
                                    // Tuần 3
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? SemesterKPI::getValueByKey('KY_'.$collect_semester.($special_case ? '_NNA' : ''))['tuan_3'] .'%' : '') : '', // Mục tiêu(Tuần 3)
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $list_weeks['tuan_3']['number_of_revenue'] : 0) : 0, // Số món thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_3',$list_weeks)) ? number_format($list_weeks['tuan_3']['revenue'],-1,'.',',') : 0) : 0, // Số thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_3',$list_weeks)) ? round($list_weeks['tuan_3']['number_of_revenue']/($total_receivables_count != 0 ? $total_receivables_count*SemesterKPI::getValueByKey('KY_'.$collect_semester.($special_case ? '_NNA' : ''))['tong']*0.01 : 1)*100 ,2).' %' : 0) : 0, // % thực tế đạt được trong tuần
                
                                    // Tuần 4
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? SemesterKPI::getValueByKey('KY_'.$collect_semester)['tuan_4'] .'%' : '') : '', // Mục tiêu(Tuần 4)
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $list_weeks['tuan_4']['number_of_revenue'] : 0) : 0, // Số món thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_4',$list_weeks)) ? number_format($list_weeks['tuan_4']['revenue'],-1,'.',',') : 0) : 0, // Số thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_4',$list_weeks)) ? round($list_weeks['tuan_4']['number_of_revenue']/($total_receivables_count != 0 ? $total_receivables_count*SemesterKPI::getValueByKey('KY_'.$collect_semester)['tong']*0.01 : 1)*100 ,2).' %' : 0) : 0, // % thực tế đạt được trong tuần
                
                                    // Tuần 5
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? SemesterKPI::getValueByKey('KY_'.$collect_semester)['tuan_5'] .'%' : '') : '', // Mục tiêu(Tuần 5)
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $list_weeks['tuan_5']['number_of_revenue'] : 0) : 0, // Số món thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_5',$list_weeks)) ? number_format($list_weeks['tuan_5']['revenue'],-1,'.',',') : 0) : 0, // Số thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_5',$list_weeks)) ? round($list_weeks['tuan_5']['number_of_revenue']/($total_receivables_count != 0 ? $total_receivables_count*SemesterKPI::getValueByKey('KY_'.$collect_semester)['tong']*0.01 : 1)*100 ,2).' %' : 0) : 0, // % thực tế đạt được trong tuần
                
                                    // Tuần 6
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? SemesterKPI::getValueByKey('KY_'.$collect_semester)['tuan_6'] .'%' : '') : '', // Mục tiêu(Tuần 6)
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $list_weeks['tuan_6']['number_of_revenue'] : 0) : 0, // Số món thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_6',$list_weeks)) ? number_format($list_weeks['tuan_6']['revenue'],-1,'.',',') : 0) : 0, // Số thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_6',$list_weeks)) ? round($list_weeks['tuan_6']['number_of_revenue']/($total_receivables_count != 0 ? $total_receivables_count*SemesterKPI::getValueByKey('KY_'.$collect_semester)['tong']*0.01 : 1)*100 ,2).' %' : 0) : 0, // % thực tế đạt được trong tuần
                                    
                                ];    
                            }
                            
                            if($has_sheet_2 == 1)
                            {
                                $has_sheet_3 = 0;
                                $sheet_2_rows[] = [
                                    !empty($dat->staff) ? $dat->staff->fullname : '', // staff
                                    $dat->code, // Lớp quản lý
                                    $dat->major->shortcode, // Ngành
                                    $dat->enrollmentObject->shortcode, // Đối tượng
                                    $collect_semester, // Kỳ thu
                                    'Vừa thu', // Trạng thái thu
                                    !empty($class_period) ? $class_period->learn_began_date->format('d/m/Y') : '', // Ngày bắt đầu học
                                    !empty($class_period) ? $class_period->collect_began_date->format('d/m/Y') : '', // Ngày bắt đầu thu
                                    !empty($class_period) ? $class_period->collect_ended_date->format('d/m/Y') : '', // Deadline ngày thu
                                    count($dat->students), // Số lượng SV đang học
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? SemesterKPI::getValueByKey('KY_'.$collect_semester.($special_case ? '_NNA' : ''))['tong'] .'%' : '') : '', // Mục tiêu kỳ
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $total_receivables_count : '') : '', // Số lượng phải thu
                                    0, // Tín chỉ
                                    number_format($price,-1,'.',',').' đ', // Đơn giá
                                    number_format(round($receivable*SemesterKPI::getValueByKey('KY_'.$collect_semester)['tong']/100,2),-1,'.',',') . ' đ', // Doanh thu dự kiến theo kế hoạch
                                    $receivables_kpi, // Số lượng phải thu theo mục tiêu
                                    number_format($sheet_2_detail['number_of_revenue'],-1,'.',','), // Số món thu tích lũy
                                    $sheet_2_detail['number_of_revenue'] - $receivables_kpi != 0 ? $sheet_2_detail['number_of_revenue'] - $receivables_kpi : '0',// Chênh lệch số món với mục tiêu
                                    $collect_semester != '' ? number_format($sheet_2_detail['total_revenue'],-1,'.',',') : 0, // Số tiền thu tích lũy
                                    
                                    // Tuần 7
                                    '', // Mục tiêu(Tuần 7)
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $list_weeks['tuan_7']['number_of_revenue'] : 0) : 0, // Số món thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_7',$list_weeks)) ? number_format($list_weeks['tuan_7']['revenue'],-1,'.',',') : 0) : 0, // Số thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_7',$list_weeks)) ? round($list_weeks['tuan_7']['number_of_revenue']/($total_receivables_count != 0 ? $total_receivables_count*SemesterKPI::getValueByKey('KY_'.$collect_semester)['tong']*0.01 : 1)*100 ,2).' %' : 0) : 0, // % thực tế đạt được trong tuần
                                    
                                    // Tuần 8
                                    '', // Mục tiêu(Tuần 8)
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $list_weeks['tuan_8']['number_of_revenue'] : 0) : 0, // Số món thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_8',$list_weeks)) ? number_format($list_weeks['tuan_8']['revenue'],-1,'.',',') : 0) : 0, // Số thực tế đã thu trong tuần
                                    $collect_semester != '' ? ((in_array('KY_'.$collect_semester,SemesterKPI::keys()) && array_key_exists('tuan_8',$list_weeks)) ? round($list_weeks['tuan_8']['number_of_revenue']/($total_receivables_count != 0 ? $total_receivables_count*SemesterKPI::getValueByKey('KY_'.$collect_semester)['tong']*0.01 : 1)*100 ,2).' %' : 0) : 0, // % thực tế đạt được trong tuần
                                ];
                            }
                            
                            if($has_sheet_3 == 1)
                            {
                                $sheet_3_rows[] = [
                                    !empty($dat->staff) ? $dat->staff->fullname : '', // staff
                                    $dat->code, // Lớp quản lý
                                    $dat->major->shortcode, // Ngành
                                    $dat->enrollmentObject->shortcode, // Đối tượng
                                    $collect_semester, // Kỳ thu
                                    'Đã thu xong', // Trạng thái thu
                                    !empty($class_period) ? $class_period->learn_began_date->format('d/m/Y') : '', // Ngày bắt đầu học
                                    !empty($class_period) ? $class_period->collect_began_date->format('d/m/Y') : '', // Ngày bắt đầu thu
                                    !empty($class_period) ? $class_period->collect_ended_date->format('d/m/Y') : '', // Deadline ngày thu
                                    count($dat->students), // Số lượng SV đang học
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? SemesterKPI::getValueByKey('KY_'.$collect_semester.($special_case ? '_NNA' : ''))['tong'] .'%' : '') : '', // Mục tiêu kỳ
                                    $collect_semester != '' ? (in_array('KY_'.$collect_semester,SemesterKPI::keys()) ? $total_receivables_count : '') : '', // Số lượng phải thu
                                    0, // Tín chỉ
                                    number_format($price,-1,'.',',').' đ', // Đơn giá
                                    number_format(round($receivable,2),-1,'.',',') . ' đ', // Doanh thu dự kiến theo kế hoạch
                                    $receivables_kpi, // Số lượng phải thu theo mục tiêu
                                    number_format($out_of_collect_time['number_of_revenue'],-1,'.',','), // Số món thu tích lũy
                                    $out_of_collect_time['number_of_revenue'] - $receivables_kpi != 0 ? $out_of_collect_time['number_of_revenue'] - $receivables_kpi : '0',// Chênh lệch số món với mục tiêu
                                    number_format($out_of_collect_time['total_revenue'],-1,'.',','), // Số tiền thu tích lũy
                                ];
                            }
                        }
       
                    }
                }
                $data->forget($index);
            }
            $data_chunks->forget($data_index);
        }
        
        if($type === 'index')
        {
            return ${'sheet_'.$search_sheet.'_rows'};
        }
        return [$sheet_1_rows,$sheet_2_rows,$sheet_3_rows];
    }
    
    private function weekTotalData()
    {
        return [
            'number_of_revenue' => 0,
            'total_revenue' => 0
        ];
    }
    
    /**
     * 
     * Check if revenue can be count in total revenue
     */
    private function isCount($profile_code,$receivable,$revenue_record,$has_count_revenue)
    {
        if($receivable - $revenue_record['paid'] >= 1000000 || (isset($has_count_revenue[$profile_code]) &&$receivable - $has_count_revenue[$profile_code] >= 1000000))
        {
            return false;
        }

        return true;
    }
}