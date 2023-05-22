<?php

namespace App\Http\Domain\Reports\Services\P825;

use App\Http\Domain\Reports\Repositories\P825\P825RepositoryInterface;
use App\Http\Domain\Reports\Requests\P825\SearchRequest;
use App\Http\Enum\StudentStatus;
use App\Http\Domain\Reports\Services\XmlGenerator;
use App\Http\Enum\LockDay;
use App\Http\Enum\StudentTypeWeek;
use Carbon\Carbon;

class P825MainService
{
    /** @var \App\Http\Domain\Reports\Repositories\P825\P825Repository */
    private $p825_repository;
    
    public function __construct(P825RepositoryInterface $p825_repository)
    {
        $this->p825_repository = $p825_repository;
    }

    public function getAll($request,$type) {
        [ $data,$chosen_semester ] = $this->p825_repository->getAll($request);
        if(!empty($data))
        {
            foreach($data as $dat)
            {
                $max_semester = $chosen_semester != '' ? $chosen_semester : max($dat->period->pluck('semester')->toArray());
                $previous_semester = $max_semester - 1;
                $list_g_date = call_user_func_array('array_merge',array_filter(array_map(function($period) use ($max_semester,$previous_semester){
                    if(in_array($period['semester'],[$max_semester,$previous_semester]))
                    {
                        return [
                            'dot_'.$period['semester'] => $period['decision_date'] != null ? $period['decision_date'] : $period['learn_began_date']
                        ];
                    }
                    return [];
                },$dat->period->toArray())));

                $students = $dat->studentClassrooms;
                
                if(isset($list_g_date['dot_'.$previous_semester]))
                {
                    $students->filter(function($record) use ($list_g_date,$previous_semester) {
                        if($record->began_at < $list_g_date['dot_'.$previous_semester] && ($record->ended_at > $list_g_date['dot_'.$previous_semester] || $record->ended_at === null))
                        {
                            return $record;
                        }
                    });
                }

                if(isset($list_g_date['dot_'.$max_semester]))
                {
                    $students->filter(function($record) use ($list_g_date,$max_semester) {
                        if($record->began_at < $list_g_date['dot_'.$max_semester] && ($record->ended_at > $list_g_date['dot_'.$max_semester] || $record->ended_at === null))
                        {
                            return $record;
                        }
                    });
                }

                $students = $students->pluck('student');
                
                $n_semester_count = 0;
                $n_1_semester_count = 0;
                $has_in_n = [];
                $has_in_n_1 = [];
                foreach($students as $student)
                {
                    if(!(($student->revisionHistories)->isEmpty()))
                    {
                        if($previous_semester != 0)
                        {
                            foreach($student->revisionHistories as $history)
                            {
                                if(isset($list_g_date['dot_'.$previous_semester]) && $history->began_at <= $list_g_date['dot_'.$previous_semester] && (($history->ended_at != null && $history->ended_at > $list_g_date['dot_'.$previous_semester]) || $history->ended_at == null) &&
                                $history->type == 2 && in_array($history->value,StudentStatus::studentInClass()))
                                {
                                    if(!isset($has_in_n_1[$student->id]))
                                    {
                                        $has_in_n_1[$student->id] = 1;
                                        $n_1_semester_count++;
                                        continue;
                                    }
                                }

                                if(isset($list_g_date['dot_'.$max_semester]) && $history->began_at <= $list_g_date['dot_'.$max_semester] && (($history->ended_at != null && $history->ended_at > $list_g_date['dot_'.$max_semester]) || $history->ended_at == null) && 
                                $history->type == 2 && in_array($history->value,StudentStatus::studentInClass()))
                                {
                                    if(!isset($has_in_n[$student->id]))
                                    {
                                        $has_in_n[$student->id] = 1;
                                        $n_semester_count++;
                                        continue;
                                    }
                                }   
                            }
                        }
                    } else {
                        if(!isset($has_count[$student->id]))
                        {
                            if($max_semester == 1 && in_array($student->student_status,StudentStatus::studentInClass()))
                            {
                                if(!isset($has_in_n[$student->id]))
                                {
                                    $has_in_n[$student->id] = 1;
                                    $n_semester_count++;
                                    continue;
                                }
                            } else if ($max_semester != 1 && in_array($student->student_status,StudentStatus::studentInClass())) {
                                if(!isset($has_in_n[$student->id]))
                                {
                                    $has_in_n[$student->id] = 1;
                                    $n_semester_count++;
                                }
                                
                                if(!isset($has_in_n_1[$student->id]))
                                {
                                    $has_in_n_1[$student->id] = 1;
                                    $n_1_semester_count++;
                                }
                            }   
                        }
                    }
                }

                $dat->n_semester = $max_semester;
                $dat->n_1_semester = $previous_semester;
                $dat->g_date = date('d/m/Y',strtotime($list_g_date['dot_'.$max_semester]));
                $dat->n_1_semester_count = $n_1_semester_count;
                $dat->n_semester_count = $n_semester_count;
                $dat->percentage = round($n_semester_count/($n_1_semester_count != 0 ? $n_1_semester_count : 1) * 100,2);
            }
        }
        
        if($type === 'index')
        {
            return $data;
        } else if ($type === 'export') {
            return $data->transform(function($dat) {
                return [
                    'staff' => $dat->staff?->fullname,
                    'major' => $dat->major?->shortcode,
                    'object' => $dat->enrollmentObject?->shortcode,
                    'class' => $dat->code,
                    'area' => $dat->area?->code,
                    'g_date' => $dat->g_date,
                    'n_1_semester' => $dat->n_1_semester,
                    'n_semester' => $dat->n_semester,
                    'n_1_semester_count' => $dat->n_1_semester_count,
                    'n_semester_count' => $dat->n_semester_count,
                    'percentage' => $dat->percentage,
                ];
            });
        }
        
    }

}