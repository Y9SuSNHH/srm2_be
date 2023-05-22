<?php

namespace App\Http\Domain\Reports\Services\F111;

use App\Eloquent\Profile;
use App\Eloquent\Student;
use App\Eloquent\StudentClassroom;
use App\Eloquent\StudentProfile;
use App\Http\Domain\Reports\Repositories\F111\F111RepositoryInterface;
use App\Http\Domain\Reports\Requests\F111\UploadRequest;
use App\Helpers\CsvParser;
use App\Http\Domain\Common\Services\StudentHistoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use App\Http\Domain\Reports\Factories\ProfileServiceInterface;
use App\Http\Domain\Reports\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Reports\Repositories\StudentProfile\StudentProfileRepositoryInterface;
use App\Http\Domain\Reports\Repositories\Profile\ProfileRepositoryInterface;
use App\Http\Domain\Reports\Repositories\StudentClassroom\StudentClassroomRepositoryInterface;
use App\Http\Enum\IdentificationDiv;
use App\Http\Enum\StudentRevisionHistoryType;
use App\Http\Enum\StudentStatus;

class F111UploadFileService
{
    private $student_repository;
    private $student_profile_repository;
    private $profile_repository;
    private $student_classroom_repository;

    public function __construct(
        StudentRepositoryInterface $student_repository,
        StudentProfileRepositoryInterface $student_profile_repository,
        ProfileRepositoryInterface $profile_repository,
        StudentClassroomRepositoryInterface $student_classroom_repository,
    ) {
        $this->student_repository = $student_repository;
        $this->student_profile_repository = $student_profile_repository;
        $this->profile_repository = $profile_repository;
        $this->student_classroom_repository = $student_classroom_repository;
    }

    public function analyzing(UploadRequest $request, F111RepositoryInterface $repository)
    {
        $paths = explode('\\', ProfileServiceInterface::class);
        $name = array_pop($paths);
        $classname = sprintf('%s\\%s', implode('\\', $paths), str_replace('Interface', '', $name));
        $service = app()->service($classname);
        $service->processUploadFile($request);
        return [
            $service->getErrors(),
            $service->getPreview(),
            $service->getProfile(),
            $service->getStudentProfile(),
            $service->getStudent(),
            $service->getStudentClassroom(),
        ];
    }

    public function store(int $storage_file_id, array $profiles, array $student_profiles, array $students, array $student_classrooms, F111RepositoryInterface $repository): bool
    { {
            $result = false;

            try {
                $result = DB::transaction(function () use ($profiles, $student_profiles, $students, $student_classrooms) {
                    $profiles = array_map(function ($item) {
                        $item['curriculum_vitae'] = json_encode($item['curriculum_vitae']);
                        return $item;
                    }, $profiles);
                    $student_profiles = array_map(function ($item) {
                        $item['documents'] = json_encode($item['documents']);
                        return $item;
                    }, $student_profiles);

                    if (!$this->profileQuery()->insert($profiles)) {
                        throw_json_response('insert student_profiles fail');
                    }

                    $identification_available = array_column(array_filter($profiles, function ($item) {
                        return $item['identification_div'] === IdentificationDiv::AVAILABLE;
                    }), 'identification');

                    $identification_unavailable = array_column(array_filter($profiles, function ($item) {
                        return $item['identification_div'] === IdentificationDiv::UNAVAILABLE;
                    }), 'identification');

                    $identifications = $this->profileQuery()
                        ->orWhere(function ($query) use ($identification_available) {
                            $query->whereIn('identification', $identification_available)
                                ->where('identification_div', IdentificationDiv::AVAILABLE);
                        })
                        ->orWhere(function ($query) use ($identification_unavailable) {
                            $query->whereIn('identification', $identification_unavailable)
                                ->where('identification_div', IdentificationDiv::UNAVAILABLE);
                        })
                        ->get(['id', 'identification', 'identification_div'])
                        ->keyBy('identification')
                        ->toArray();
                    $student_profiles = array_map(function ($item) use ($identifications) {
                        if (isset($item['identification']) && isset($item['identification_div'])) {
                            if (isset($identifications[$item['identification']]) && $item['identification_div'] === $identifications[$item['identification']]['identification_div']) {
                                $item['profile_id'] = $identifications[$item['identification']]['id'];
                            }

                            unset($item['identification']);
                            unset($item['identification_div']);
                        }
                        return $item;
                    }, $student_profiles);

                    if (!StudentProfile::query()->insert($student_profiles)) {
                        throw_json_response('insert student_profiles fail');
                    }

                    $student_profile_ids = $this->studentProfileQuery()
                        ->whereIn('profile_code', array_column($student_profiles, 'profile_code'))
                        ->pluck('id', 'profile_code')->toArray();

                    $students = array_map(function ($item) use ($student_profile_ids) {
                        if (isset($item['profile_code'])) {
                            if ($student_profile_ids[$item['profile_code']]) {
                                $item['student_profile_id'] = $student_profile_ids[$item['profile_code']];
                            }

                            unset($item['profile_code']);
                        }
                        if (school()->getCode() === 'UNETI') {
                            unset($item['account']);
                            unset($item['email']);
                        }
                        return $item;
                    }, $students);
                    
                    if (!$this->studentQuery()->insert($students)) {
                        throw_json_response('insert students fail');
                    }

                    $student_ids = $this->studentQuery()->join('student_profiles', 'students.student_profile_id', '=', 'student_profiles.id')
                        ->whereIn('student_profiles.profile_code', array_column($student_profiles, 'profile_code'))
                        ->pluck('students.id', 'student_profiles.profile_code')->toArray();

                    $student_classrooms = array_map(function ($item) use ($student_ids) {
                        if (isset($item['profile_code'])) {
                            if ($student_ids[$item['profile_code']]) {
                                $item['student_id'] = $student_ids[$item['profile_code']];
                            }

                            unset($item['profile_code']);
                        }
                        return $item;
                    }, $student_classrooms);
                    
                    if (!$this->studentClassroomQuery()->insert($student_classrooms)) {
                        throw new \Exception('insert student_classrooms fail');
                    }
                    
                    $student_history_ids = array_values($student_ids);
                    $student_history_service = app()->service(StudentHistoryService::class);
                    $student_history_service->saveStudentRevisionHistories(
                        StudentRevisionHistoryType::STUDENT_STATUS,
                        $student_history_ids,
                        StudentStatus::DANG_HOC_CHUA_HS
                    );
                    return true;
                });
            } catch (\Exception $exception) {
                throw_json_response($exception->getMessage());
            }

            return $result;
        }
    }

    public function profileQuery()
    {
        return Profile::query();
    }
    public function studentQuery()
    {
        return Student::query();
    }
    public function studentProfileQuery()
    {
        return StudentProfile::query();
    }
    public function studentClassroomQuery()
    {
        return StudentClassroom::query();
    }
}
