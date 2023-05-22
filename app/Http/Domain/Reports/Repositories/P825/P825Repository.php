<?php

namespace App\Http\Domain\Reports\Repositories\P825;

use App\Eloquent\Student;
use App\Eloquent\Period;
use App\Eloquent\Staff;
use App\Eloquent\Major;
use App\Eloquent\Classroom as EloquentClassroom;
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

class P825Repository implements P825RepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll($request)
    {
        $from = isset($request['from']) ? $request['from'] : '';
        $to = isset($request['to']) ? $request['to'] : '';
        $g_date = isset($request['g_date']) ? $request['g_date'] : '';
        $semester = isset($request['semester']) ? $request['semester'] : '';
        $classes = isset($request['classes']) ? $request['classes'] : [];
        $query = EloquentClassroom::query()->with(['period','studentClassrooms' => function($q) {
            $q->with(['student' => function($q) {
                $q->with('revisionHistories');
            }]);
        },'staff','area','major','enrollmentObject'])
        ->whereHas('period',function($q) {
            $q->where('semester','<>',1);
        });
        if($from !== '')
        {
            $query->whereHas('period', function ($q) use($from){
                $q->where('decision_date','>=',$from)->orWhere(function($q) use($from){
                    $q->whereNull('decision_date')->where('learn_began_date','>=',$from);
                });
            });
        }
        
        if($to !== '')
        {
            $query->whereHas('period',function($q) use ($to) {
                $q->where('decision_date','<=',$to)->orWhere(function($q) use($to){
                    $q->whereNull('decision_date')->where('learn_began_date','<=',$to);
                });
            });
        }

        if($g_date !== '')
        {
            $query->whereHas('period',function($q) use ($g_date) {
                $q->where('decision_date','=',$g_date)->orWhere(function($q) use($g_date){
                    $q->whereNull('decision_date')->where('learn_began_date','=',$g_date);
                });
            });
        }

        if($semester !== '')
        {
            $query->whereHas('period',function($q) use ($semester) {
                $q->where('semester',$semester);
            });
        }

        
        if(!empty($classes))
        {
            $query->whereIn('id',$classes);
        }
        $query->orderBy('area_id')->orderBy('major_id')->orderBy('id');
        return [isset($request['per_page']) ? $query->makePaginate($request['per_page']) : $query->get(),$semester ];
    }
}