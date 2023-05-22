<?php

namespace App\Http\Domain\Reports\Services\G120;

use App\Http\Domain\Reports\Repositories\G120\G120RepositoryInterface;
use App\Http\Enum\ProfileStatus;
use App\Http\Enum\LockDay;
use App\Http\Enum\StudentTypeWeek;
use App\Http\Enum\WeekClassifications;
use App\Http\Enum\Level;
use Carbon\Carbon;

class ManageEngagementProcessesService
{
    /** @var \App\Http\Domain\Reports\Repositories\G120\G120Repository */
    private $g120_repository;

    public function __construct(G120RepositoryInterface $g120_repository)
    {
        $this->g120_repository = $g120_repository;
    }

    public function updateQuery($request)
    {
        $data = $request['data'];
        $params = $request['params'];
        $student_ids = array_filter(array_map(function($student) {
            return $student['id'];
        },$data));

        [$students,$actual_collected] = $this->g120_repository->getActualCollected($student_ids);
        $is_lock_week1_rating = LockDay::isLockWeek1Rating(Carbon::parse($params['keywords'] ? null : $params['first_day_of_school']));
        $is_lock_week4_rating = LockDay::isLockWeek4Rating(Carbon::parse($params['keywords'] ? null : $params['first_day_of_school']));
        $studentTypes = $this->getStudentType($students,$data,$actual_collected);
        $update_data = array_map(function($student) {
            $now = Carbon::now()->toDateTimeString();
            return [
                $student['student_id'],
                $student['is_join_first_day_of_school'] != null ? $student['is_join_first_day_of_school'] : 0,
                $student['is_join_first_week'] != null ? $student['is_join_first_week'] : 0,
                $student['is_join_fourth_week'] != null ? $student['is_join_fourth_week'] : 0,
                $student['student_type_week_1'],
                $student['student_type_week_4'],
                1,
                "'$now'::TIMESTAMP"
            ];
        },$studentTypes);
        $notes_to_update = array_map(function($student) {
            return [
                $student['id'],
                "'".$student['note']."'",
            ];
        },$data);
        
        $values = "(".implode('), (', array_map(function ($array) {
            return implode(', ', $array);
        }, $update_data)).")";

        $notes = "(".implode('), (', array_map(function ($array) {
            return implode(', ', $array);
        }, $notes_to_update)).")";
        $update_query = "insert into learning_engagement_processes as lep (student_id, is_join_first_day_of_school, is_join_first_week, is_join_fourth_week, student_type_first_week, student_type_fourth_week, modified_by, last_modified) values $values on conflict (student_id) do update set ".
                        ($is_lock_week1_rating ? '' : 'is_join_first_day_of_school = excluded.is_join_first_day_of_school,') .
                        ($is_lock_week1_rating ? '' : 'is_join_first_week=excluded.is_join_first_week,') .
                        ($is_lock_week4_rating ? '' : 'is_join_fourth_week=excluded.is_join_fourth_week,') .
                        'student_type_first_week=excluded.student_type_first_week,' .
                        'student_type_fourth_week=excluded.student_type_fourth_week,' .
                        'modified_by=excluded.modified_by, ' .
                        'last_modified=excluded.last_modified ';
        $update_note = "update students st set note = u.note
                        from (values $notes) as u(student_id,note) where u.student_id = st.id";
        try {
            $execute_sql = $this->g120_repository->executeUpdate($update_query);
            $execute_update_note = $this->g120_repository->executeUpdate($update_note);
        } catch (\Exception $e) {
            return [
                'successful' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ];
        }
        return [
            'successful' => true,
            'message'    => 'Đã xếp hạng thành công',
        ];
    }

    
    public function getStudentType($students,$inputs,$actual_collected)
    {
        $student_type_arr = [];
        foreach($students as $student)
        {
            [$difference, $paid, $receivable, $has_money] = $this->studentFeesDistinguishment($student,$actual_collected);
            $student_type_week_4 ='';
            $student_input = call_user_func_array('array_merge',array_filter(array_map(function($input) use ($student) {
                if($input['id'] === $student->id)
                    return $input;
                return [];
            },$inputs)));

            $input_for_classification = [
                'difference'                    => $difference >= 0 ? 1 : 0,
                'has_certain_profile_status'    => ProfileStatus::search($student->profile_status) === 'QDNH_HS_CUNG' ? 1 : 0,
                'study_later'                   => $student_input['study_later'] != null ? $student_input['study_later'] : 0,
                'is_join_first_day_of_school'   => $student_input['is_join_first_day_of_school'] != null ? $student_input['is_join_first_day_of_school'] : 0,
                'is_join_first_week'            => $student_input['is_join_first_week'] != null ? $student_input['is_join_first_week'] : 0,
                'is_join_fourth_week'           => $student_input['is_join_fourth_week'] != null ? $student_input['is_join_fourth_week'] : 0,
            ];

            $student_type_week_1 = WeekClassifications::makeClassification(1,$input_for_classification);

            if ($has_money && $difference >= 0) {
                if (ProfileStatus::isF30($student->profile_status)) {
                    $student_type_week_4 = 'B2_HS';
                } else {
                    $student_type_week_4 = WeekClassifications::makeClassification(3,$input_for_classification);
                }
            } else {
                $student_type_week_4 = 'C';
            }
            $first_day = $student_input['is_join_first_day_of_school'];
            array_push($student_type_arr,[
                'student_id'                    => $student->id,
                'is_join_first_day_of_school'   => $student_input['is_join_first_day_of_school'],
                'is_join_first_week'            => $student_input['is_join_first_week'],
                'is_join_fourth_week'           => $student_input['is_join_fourth_week'],
                'student_type_week_1'           => StudentTypeWeek::getValueByKey($student_type_week_1),
                'student_type_week_4'           => StudentTypeWeek::getValueByKey($student_type_week_4),
                'receivable'                    => $receivable,
                'paid'                          => $paid
            ]);
        }

        return $student_type_arr;
    }

    public static function studentFeesDistinguishment($student,$actual_collected)
    {
        $receivable = !($student->studentProfile->receivable->isEmpty()) ? $student->studentProfile->receivable->first()->receivable : 0;
        $paid = $actual_collected[$student->studentProfile->profile_code] ?? 0;
        $difference = $paid - $receivable;
        $has_money = $paid > 0 && $receivable > 0;
        return [$difference, $paid, $receivable, $has_money];
    }

    public function addRevenue($students)
    {
        [$student_data,$student_revenues] = $this->g120_repository->getActualCollected($students->pluck('id'));
        $students = $students->map(function ($student) use ($student_revenues) {
            $student['first_semester_revenue'] = $student_revenues[$student['profile_code']];
            $student['differencial'] = $student['first_semester_revenue'] - $student['first_semester_receivable'];
            $student['level'] = $this->getLevel($student['first_semester_receivable'],$student['first_semester_revenue']);
            return $student;
        });

        return $students;
    }

    public function getLevel($receivable,$revenue) {
        $has_money = $revenue > 0 && $receivable > 0;
        $difference = $revenue - $receivable;
        return !$has_money ? LEVEL::search(5) : ($difference >= 0 ? LEVEL::search(8) : ($difference = -$receivable ? LEVEL::search(6) : LEVEL::search(5)));
    }
}