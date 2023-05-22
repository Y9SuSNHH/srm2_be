<?php

namespace App\Http\Domain\Receivable\Services;

use Carbon\Carbon;
use App\Http\Enum\TuitionFee;
use App\Http\Domain\Receivable\Repositories\Receivable\ReceivableRepositoryInterface;
use App\Http\Enum\ReceivablePurpose;

class ReceivableService
{
    private $receivable_repository;
    
    public function __construct(ReceivableRepositoryInterface $receivable_repository)
    {
        $this->receivable_repository = $receivable_repository;
    }

    public function findClassroomReceivable($id) 
    {
        $classroom_receivable = $this->receivable_repository->findClassroomReceivable($id);
        // dd($classroom_receivable);
        $data = [];
        $classroom = optional($classroom_receivable)->classroom ?? null;
        // dd($classroom);
        if (!$classroom_receivable || !$classroom) {
            return [];
        }

        $students = $classroom->students ?? collect();
//        dd($students);
        if ($students->isNotEmpty()) {
            foreach ($students as $student) {
            
                $student_profile = optional($student->studentProfile);
//                 dd($student_profile->profile_code);
                $profile = optional($student_profile->getProfile);
//                 dd($student->studentProfile->receivable);
                $student_receivable = $student->studentProfile->studentReceivables
                    ->where('purpose', $classroom_receivable['purpose'])
                    ->where('learning_wave_number', $classroom_receivable['semester'])
                    ->first();
                // dd($classroom->area->name  );
                $first_day_of_school = optional($classroom->enrollmentWave->first())->first_day_of_school;
                // dd($first_day_of_school);
                $data[] = [
                    'area' => $classroom->area->name ?? '',
                    'date' => $first_day_of_school ? Carbon::parse($first_day_of_school)->format('d/m/Y') : '', //'Ngày khai giảng',
                    'student_profile_code' => $student_profile->profile_code, // 'Mã hồ sơ',
                    'student_profile_id' => $student_profile->id,
                    'classroom' => $classroom->code, //'Mã lớp',
                    'fullname' => "{$student->studentProfile->getProfile->firstname} {$student->studentProfile->getProfile->lastname}",
                    'birthday' => $profile->birthday ? Carbon::parse($profile->birthday)->format('d/m/Y') : $profile->borned_year, // 'Ngày sinh',
                    'staff' => optional($classroom->staff->first())->fullname, // 'QLHT',
                    'semester' => $student_receivable ? $student_receivable->learning_wave_number : '', // 'Đợt học',
                    'purpose' => $student_receivable ? $student_receivable->purpose : 1, //'Mục đích thu',
                    'so_tien' => $student_receivable ? $student_receivable->receivable : 0, //'Phải thu',
                    'note' => $student_receivable ? $student_receivable->note : '', //'Ghi chú',
                    'student_receivable_id' => $student_receivable ? $student_receivable->id : '',
                ];
            }
        }
//        dd($data);
        return $data;
    }

    public function storeClassroomReceivable($request) 
    {
        $semester     = $request->input[0]['semester'] ?? null;
        $purpose      = $request->input[0]['purpose'] ?? null;
        $so_tien      = $request->input[0]['so_tien'] ?? null;
        $now = Carbon::now();

        $students = $this->receivable_repository->storeClassroomReceivable($request);
        $student_receivable_attributes = [];
        $update_data = [];

        foreach ($students as $student) {
            
            $student_receivable = $student->studentProfile->studentReceivables->first();
            if (!$student_receivable) {
                $student_receivable_attributes[] = [
                    'student_profile_id' => $student->studentProfile->id,
                    'receivable' => $so_tien,
                    'purpose' => $purpose,
                    'learning_wave_number' => $semester,
                    'created_at' => $now
                ];
            } else {
                $update_data[] = [
                    'id' => $student_receivable->id,
                    'receivable' => $so_tien,
                ];
            }
        }
        $this->receivable_repository->insertOrUpdateStudentReceivable($student_receivable_attributes, $update_data);
        return true;
    }
   

}