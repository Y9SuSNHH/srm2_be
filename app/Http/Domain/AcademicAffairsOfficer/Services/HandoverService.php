<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Services;

use App\Http\Domain\AcademicAffairsOfficer\Repositories\Handover\HandoverRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\StudentProfile\StudentProfileRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\DeleteRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\SearchRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\SearchStudentRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\StoreRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\UpdateRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\UpdateStudentProfilesRequest;
use App\Http\Domain\Common\Services\StudentHistoryService;
use App\Http\Enum\HandoverStatus;
use App\Http\Enum\ProfileStatus;
use App\Http\Enum\ReferenceType;
use App\Http\Enum\StudentRevisionHistoryType;
use App\Http\Enum\StudentStatus;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ReflectionException;

class HandoverService
{
    private HandoverRepositoryInterface $repository;
    private StudentProfileRepositoryInterface $student_profile_repository;
    private StudentRepositoryInterface $student_repository;

    public function __construct(HandoverRepositoryInterface $handover_repository, StudentProfileRepositoryInterface $student_profile_repository, StudentRepositoryInterface $student_repository)
    {
        $this->repository                 = $handover_repository;
        $this->student_profile_repository = $student_profile_repository;
        $this->student_repository         = $student_repository;
    }

    /**
     * @param int $id
     * @param Request $request
     * @return array
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function deletedById(int $id, Request $request): array
    {
        $rules = [];
        if ($this->repository->getWithCountStudentProfile($id) > 0) {
            $rules = [
                'student_status' => [
                    'required',
                    Rule::in(StudentStatus::toArray()),
                ],
                'profile_status' => [
                    'required',
                    Rule::in(ProfileStatus::toArray()),
                ],
            ];
        }
        $messages  = [
            'required' => 'Trường :attribute bắt buộc điền',
            'in'       => 'Trường :attribute không hợp lệ',
        ];
        $attribute = [
            'student_status' => 'trạng thái sinh viên',
            'profile_status' => 'trạng thái hồ sơ',
        ];
        $validator = Validator::make($request->all(), $rules, $messages, $attribute);
        if ($validator->fails()) {
            throw_json_response($validator->messages());
        }
        $validated = $validator->validated();
        DB::transaction(function () use ($id, $validated) {
            if (!empty($validated)) {
                $student_ids = $this->student_repository->getAllByStudentProfileHandoverId($id, ['id'])->pluck('id')->toArray();
                /** @var StudentHistoryService $service */
                $service = app()->service(StudentHistoryService::class);
                $service->saveStudentRevisionHistories(StudentRevisionHistoryType::STUDENT_STATUS, $student_ids, $validated['student_status'], null, ReferenceType::HANDOVER, $id);
                $this->student_repository->updateByIds($student_ids, [
                    'profile_status' => $validated['profile_status'],
                    'student_status' => $validated['student_status'],
                ]);
                $this->student_profile_repository->updateByHandoverId($id, ['handover_id' => null]);
            }
            $this->repository->destroy($id);
        });
        return [];
    }

    /**
     * @param int $student_profile_id
     * @param DeleteRequest $request
     * @return array
     * @throws ValidationException
     */
    public function deleteStudent(int $student_profile_id, DeleteRequest $request): array
    {
        $validated = $request->validated();
        DB::transaction(function () use ($student_profile_id, $validated) {
            $student_profile = $this->student_profile_repository->getById($student_profile_id, ['id', 'handover_id']);
            if (!$student_profile->handover_id) {
                throw_json_response(['Sinh viên không có trong đợt bàn giao']);
            }
            if ($student_profile->student->id) {
                $student_id = $student_profile->student->id;
                /** @var StudentHistoryService $service */
                $service = app()->service(StudentHistoryService::class);
                $service->saveStudentRevisionHistories(StudentRevisionHistoryType::STUDENT_STATUS, $student_id, $validated['student_status'], null, ReferenceType::HANDOVER, $student_profile->handover_id);
                $this->student_repository->updateById($student_id, [
                    'profile_status' => $validated['profile_status'],
                    'student_status' => $validated['student_status'],
                ]);
            }
            $this->student_profile_repository->updateById($student_profile->id, ['handover_id' => null]);
        });
        return [];
    }

    /**
     * @param int $id
     * @param UpdateStudentProfilesRequest $request
     * @return array
     * @throws ValidationException
     */
    public function updateStudentProfileByIds(int $id, UpdateStudentProfilesRequest $request, SearchStudentRequest $search_request): array
    {
        $validated = $request->validated();

        DB::transaction(function () use ($id, $validated, $search_request) {
            $handover = $this->repository->getById($id, ['id', 'student_status', 'profile_status']);
            if ($validated['check_all'] === true) {
                $student_ids = $this->repository->getStudentById($id, $search_request, ['id', 'student_profile_id'])->pluck('id')->toArray();
            } else {
                $student_ids = $this->student_repository->getAllByStudentProfileId($validated['student_profile_ids'], ['id'])->pluck('id')->toArray();
            }

            /** @var StudentHistoryService $service */
            $update_student = [];
            if ($handover->student_status) {
                $update_student['student_status'] = $handover->student_status;

                $service = app()->service(StudentHistoryService::class);
                $service->saveStudentRevisionHistories(StudentRevisionHistoryType::STUDENT_STATUS, $student_ids, $handover->student_status, null, ReferenceType::HANDOVER, $id);
            }

            if ($handover->profile_status) {
                $update_student['profile_status'] = $handover->profile_status;
            }

            if ($update_student) {
                $this->student_repository->updateByIds($student_ids, $update_student);
            }

            return $this->student_profile_repository->updateByIds($validated['student_profile_ids'], ['handover_id' => $id]);
        });

        return [];
    }

    /**
     * @param int $id
     * @return void
     */
    public function checkIsLockById(int $id): void
    {
        if ($this->repository->getIsLockById($id)) {
            throw_json_response('Đợt bàn giao đã bị khóa');
        }
    }

    /**
     * @param int $id
     * @param UpdateRequest $request
     * @return array
     * @throws ValidationException
     */
    public function updateById(int $id, UpdateRequest $request): array
    {
        $validated = $request->validated();
        DB::transaction(function () use ($id, $validated) {
            $handover = $this->repository->getById($id, ['id', 'student_status', 'profile_status']);

            $update_student = [];
            if ($handover->student_status !== $validated['student_status']) {
                $update_student['student_status'] = $validated['student_status'];
            }
            if ($handover->profile_status !== $validated['profile_status']) {
                $update_student['profile_status'] = $validated['profile_status'];
            }

            if ($update_student) {
                $student_profile_ids = $this->repository->getStudentProfileIdInHandover($id, true)->toArray();
                $student_ids         = $this->student_repository->getAllByStudentProfileId($student_profile_ids, ['id'])->pluck('id')->toArray();
                if (array_key_exists('student_status', $update_student)) {
                    $service = app()->service(StudentHistoryService::class);
                    $service->saveStudentRevisionHistories(StudentRevisionHistoryType::STUDENT_STATUS, $student_ids, $validated['student_status'], null, ReferenceType::HANDOVER, $id);
                }
                $this->student_repository->updateByIds($student_ids, $update_student);
            }
            return $this->repository->updateById($id, $validated);
        });
        return [];
    }
}