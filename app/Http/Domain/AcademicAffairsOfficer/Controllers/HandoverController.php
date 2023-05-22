<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Controllers;

use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Handover\HandoverRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\StudentProfile\StudentProfileRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\StudentProfile\StudentProfileRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\DeleteRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\SearchRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\SearchStudentRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\StoreRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\UpdateLockRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\UpdateRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\UpdateStudentProfilesRequest;
use App\Http\Domain\AcademicAffairsOfficer\Services\HandoverService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ReflectionException;
use Throwable;

class HandoverController
{
    use StepByStep, FileDownloadAble;

    private HandoverRepositoryInterface $repository;
    private HandoverService $service;
    private StudentProfileRepositoryInterface $student_profile_repository;

    public function __construct(HandoverRepositoryInterface $handover_repository, HandoverService $handover_service, StudentProfileRepositoryInterface $student_profile_repository)
    {
        $this->repository                 = $handover_repository;
        $this->student_profile_repository = $student_profile_repository;
        $this->service                    = $handover_service;
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->repository->getAll($request));
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws ReflectionException
     * @throws Exception
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->repository->store($request));
    }


    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->service->updateById($id, $request));
    }

    /**
     * @param UpdateLockRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws ReflectionException
     * @throws Throwable
     */
    public function updateLock(UpdateLockRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->repository->updateById($id, $request->validated()));
    }

    /**
     * @param SearchStudentRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Exception
     */
    public function indexStudent(SearchStudentRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->repository->getByIdWithStudentProfiles($id, $request));
    }

    /**
     * @param UpdateStudentProfilesRequest $request
     * @param SearchStudentRequest $search_request
     * @param int $id
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function updateStudent(UpdateStudentProfilesRequest $request, SearchStudentRequest $search_request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        $this->service->checkIsLockById($id);
        return json_response(true, $this->service->updateStudentProfileByIds($id, $request, $search_request));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        return json_response(true, $this->service->deletedById($id, $request));
    }

    /**
     * @param int $id
     * @param DeleteRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function destroyStudent(int $id, DeleteRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->service->deleteStudent($id, $request));
    }
}