<?php

namespace App\Http\Domain\Student\Services;

use App\Http\Domain\AcademicAffairsOfficer\Services\ClassService;
use App\Http\Domain\Student\Repositories\StudentClassroom\StudentClassroomRepository;
use App\Http\Domain\Student\Repositories\StudentClassroom\StudentClassroomRepositoryInterface;
use App\Http\Domain\Student\Repositories\StudentRevisionHistory\StudentRevisionHistoryRepository;
use App\Http\Domain\Student\Repositories\StudentRevisionHistory\StudentRevisionHistoryRepositoryInterface;
use App\Http\Enum\StudentStatus;
use Carbon\Carbon;

/**
 * Class StudentHistoryService
 * @package App\Http\Domain\Student\Services
 */
class StudentHistoryService
{
    /** @var StudentRevisionHistoryRepository */
    private $student_revision_history_repository;
    /** @var StudentClassroomRepository */
    private $student_classroom_repository;

    /**
     * StudentHistoryService constructor.
     * @param StudentRevisionHistoryRepositoryInterface $student_revision_history_repository
     * @param StudentClassroomRepositoryInterface $student_classroom_repository
     */
    public function __construct(
        StudentRevisionHistoryRepositoryInterface $student_revision_history_repository,
        StudentClassroomRepositoryInterface       $student_classroom_repository
    )
    {
        $this->student_revision_history_repository = $student_revision_history_repository;
        $this->student_classroom_repository        = $student_classroom_repository;
    }

    /**
     * @param int $student_id
     * @return array
     * @throws \ReflectionException
     */
    public function getAll(int $student_id): array
    {
        $result             = [];
        $student_statuses   = $this->student_revision_history_repository->fetchStudentStatusByDate($student_id);
        $student_classrooms = $this->student_classroom_repository->fetchByDate($student_id);
        /** @var ClassService $class_service */
        $class_service = app()->service(ClassService::class);
        /** @var \App\Eloquent\Classroom[]|\Illuminate\Database\Eloquent\Collection $classrooms */
        $classrooms         = $class_service->findAll($student_classrooms->pluck('classroom_id')->toArray());
        $student_classrooms = $student_classrooms->map(function ($item) use ($classrooms) {
            $classroom       = $classrooms->where('id', $item->classroom_id)->first();
            $item->area      = $classroom->area->code;
            $item->classroom = $classroom->code;
            return $item;
        });

        foreach ($student_statuses as $student_status) {
            if (!isset($result[$student_status->began_date])) {
                $result[$student_status->began_date] = ['beganDate' => Carbon::parse($student_status->began_date)->toAtomString()];
            }
            $result[$student_status->began_date]['studentStatus'] = StudentStatus::from((int)$student_status->value)->getLang();
            $result[$student_status->began_date]['no']            = $student_status->no;
            $result[$student_status->began_date]['effectiveDate'] = $student_status->effective_date ? Carbon::parse($student_status->effective_date)->toAtomString() : null;
        }

//            dd($student_classrooms);
        foreach ($student_classrooms as $student_classroom) {
            if (!isset($result[$student_classroom->began_date])) {
                $result[$student_classroom->began_date] = ['beganDate' => Carbon::parse($student_classroom->began_date)->toAtomString()];
            }

            $result[$student_classroom->began_date]['area']      = $student_classroom->area;
            $result[$student_classroom->began_date]['classroom'] = $student_classroom->classroom;
        }
        ksort($result);
        $result             = array_values($result);
        $area               = array_column($result, 'area')[0] ?? '';
        $old_classroom      = array_column($result, 'classroom')[0] ?? '';
        $old_student_status = array_column($result, 'studentStatus')[0] ?? '';
        for ($i = 0; $i < count($result); $i++) {
            if (!isset($result[$i]['classroom'])) {
                $result[$i]['classroom'] = $old_classroom;
                $result[$i]['area']      = $area;
            }

            $result[$i]['oldClassroom'] = $old_classroom;

            if ($result[$i]['classroom'] !== $old_classroom) {
                $old_classroom = $result[$i]['classroom'];
                $area          = $result[$i]['area'];
            }

            if (!isset($result[$i]['studentStatus'])) {
                $result[$i]['studentStatus'] = $old_student_status;
            }

            $result[$i]['oldStudentStatus'] = $old_student_status;

            if ($result[$i]['studentStatus'] !== $old_student_status) {
                $old_student_status = $result[$i]['studentStatus'];
            }

            if ($i === 0 && array_key_exists('oldClassroom',$result[$i])) {
                $result[$i]['oldArea'] = $result[$i]['area'];
            } else {
                $result[$i]['oldArea'] = $result[$i - 1]['area'];
            }
        }

        return $result;
    }
}
