<?php

namespace App\Http\Domain\Common\Services;

use App\Http\Domain\Common\Repositories\StudentClassroom\StudentClassroomRepository;
use App\Http\Domain\Common\Repositories\StudentClassroom\StudentClassroomRepositoryInterface;
use App\Http\Domain\Common\Repositories\StudentRevisionHistory\StudentRevisionHistoryRepository;
use App\Http\Domain\Common\Repositories\StudentRevisionHistory\StudentRevisionHistoryRepositoryInterface;
use App\Http\Enum\ReferenceType;
use App\Http\Enum\StudentRevisionHistoryType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentHistoryService
{
    /** @var StudentRevisionHistoryRepository */
    private $student_revision_history_repository;
    /** @var StudentClassroomRepository */
    private $student_classroom_repository;

    public function __construct(StudentRevisionHistoryRepositoryInterface $repository, StudentClassroomRepositoryInterface $classroom)
    {
        $this->student_revision_history_repository = $repository;
        $this->student_classroom_repository        = $classroom;
    }

    /**
     * @param array|int $students
     * @param int $classroom_id
     * @param Carbon|null $began_at
     * @param int|null $petition_id
     * @param int|null $user_id
     * @return false
     */
    public function saveStudentClassroomLog(array|int $students, int $classroom_id, Carbon $began_at = null, int $petition_id = null, int $user_id = null): bool
    {
        $result = false;

        try {
            if (is_array($students)) {
                $students = array_unique($students);
            }

            $result = DB::transaction(function () use ($students, $classroom_id, $began_at, $petition_id, $user_id) {
                [$students, $began_at, $ended_at, $began_date, $now] = $this->retrieveInput($students, $began_at);

                $this->student_classroom_repository->updateEnded($ended_at, $students, $user_id);

                $insert_attributes = array_map(function ($student_id) use ($classroom_id, $began_at, $began_date, $petition_id, $now) {
                    return [
                        'student_id'     => $student_id,
                        'classroom_id'   => $classroom_id,
                        'began_at'       => $began_at,
                        'began_date'     => $began_date,
                        'created_at'     => $now,
                        'created_by'     => $user_id ?? auth()->getId(),
                        'reference_type' => $petition_id ? ReferenceType::PETITION : 0,
                        'reference_id'   => $petition_id,
                    ];
                }, $students);
                $this->student_classroom_repository->insert($insert_attributes);

                return true;
            });
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
            Log::error('Fail to save student_revision_histories', compact('students', 'classroom_id', 'began_at', 'petition_id'));
        }

        return $result;
    }

    /**
     * @param int $type
     * @param array|int $students
     * @param $value
     * @param Carbon|null $began_at
     * @param int|null $petition_id
     * @param int|null $user_id
     * @return false
     */
    public function saveStudentRevisionHistories(int $type, array|int $students, $value, Carbon $began_at = null, int $petition_id = null, int $user_id = null): bool
    {
        if (StudentRevisionHistoryType::CLASSROOM === $type) {
            return $this->saveStudentClassroomLog($students, (int)$value, $began_at, $petition_id, $user_id);
        }

        $result = false;
        try {
            if (is_array($students)) {
                $students = array_unique($students);
            }

            $result = DB::transaction(function () use ($type, $students, $value, $began_at, $petition_id, $user_id) {
                [$students, $began_at, $ended_at, $began_date, $now] = $this->retrieveInput($students, $began_at);
                $this->student_revision_history_repository->updateEnded($ended_at, $students, $type, $user_id);

                $insert_attributes = array_map(function ($student_id) use ($type, $value, $began_at, $began_date, $petition_id, $now) {
                    return [
                        'student_id'     => $student_id,
                        'type'           => $type,
                        'value'          => $value,
                        'began_at'       => $began_at,
                        'began_date'     => $began_date,
                        'created_at'     => $now,
                        'created_by'     => $user_id ?? auth()->getId(),
                        'reference_type' => $petition_id ? ReferenceType::PETITION : 0,
                        'reference_id'   => $petition_id,
                    ];
                }, $students);

                $this->student_revision_history_repository->insert($insert_attributes);

                return true;
            });
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
            Log::error('Fail to save student_revision_histories', compact('type', 'students', 'value', 'began_at', 'petition_id'));
        }

        return $result;
    }

    /**
     * @param int|array $students
     * @param Carbon|null $date
     * @return array
     */
    private function retrieveInput(array|int $students, Carbon $date = null): array
    {
        if (!is_array($students)) {
            $students = [(int)$students];
        }

        $now        = Carbon::now();
        $began_at   = is_null($date) ? $now : $date;
        $ended_at   = $began_at->copy()->subSecond();
        $began_date = $began_at->copy()->toDateString();

        return [$students, $began_at, $ended_at, $began_date, $now];
    }
}