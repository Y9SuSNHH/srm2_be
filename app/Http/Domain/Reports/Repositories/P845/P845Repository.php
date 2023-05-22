<?php

namespace App\Http\Domain\Reports\Repositories\P845;

use App\Eloquent\Classroom as EloquentClassroom;
use App\Eloquent\Crm\Student as CrmStudent;
use App\Http\Enum\StudentReceivablePurpose;

class P845Repository implements P845RepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll($request)
    {
        $staff = isset($request['staff']) ? $request['staff'] : '';
        $major = isset($request['major']) ? $request['major'] : '';
        $collect_semester = isset($request['collect_semester']) ? $request['collect_semester'] : '';

        $query = EloquentClassroom::query()->with(['students' => function($q) {
            $q->with(['studentProfile' => function ($q) {
                $q->with('receivable');
            }]);
        },'staff','major','enrollmentObject','period','enrollmentWave']);

        if($staff != '')
        {
            $query->whereHas('staff',function($q) use($staff){
                $q->where('id',$staff);
            });
        }

        if($major != '')
        {
            $query->whereHas('major',function($q) use($major){
                $q->where('id',$major);
            });
        }

        if($collect_semester != '')
        {
            $query->whereHas('period',function($q) use($collect_semester){
                $q->where('semester',$collect_semester);
            });
        }

        return [$query->get(),$collect_semester];
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
                $thuc_thu['semester_'.$thucthu->dot_hoc_so][] = [
                    'semester' => $thucthu->dot_hoc_so,
                    'paid' => $thucthu->thuc_nop,
                    'receipt_date' => $thucthu->ngay_bien_lai
                ];
            }
            
            $thuc_thu = !empty($thuc_thu) ? array_map(function($tt) {
                return $tt;
            },$thuc_thu) : [];

            return [$sv->ma_ho_so => $thuc_thu];
        });
        $all_actual_revenues = call_user_func_array('array_merge',array_map(function($tt) {
            return $tt;
        },$CrmStudents->toArray()));

        return $all_actual_revenues;
    }
}