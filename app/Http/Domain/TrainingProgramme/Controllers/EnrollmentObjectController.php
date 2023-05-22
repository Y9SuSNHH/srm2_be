<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Http\Domain\TrainingProgramme\Repositories\EnrollmentObject\EnrollmentObjectRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\EnrollmentObject\CreateEnrollmentObjectRequest;
use App\Http\Domain\TrainingProgramme\Requests\EnrollmentObject\SearchRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;

/**
 * Class ObjectController
 * @package App\Http\Domain\TrainingProgramme\Controllers
 */
class EnrollmentObjectController extends Controller
{
    /**
     * @param SearchRequest $request
     * @param EnrollmentObjectRepositoryInterface $enrollment_object_repository
     * @return JsonResponse
     * @throws Exception
     */
    public function index(SearchRequest $request, EnrollmentObjectRepositoryInterface $enrollment_object_repository): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $enrollment_object_repository->getAll($request));
    }

    /**
     * @param SearchRequest $request
     * @param EnrollmentObjectRepositoryInterface $enrollment_object_repository
     * @return JsonResponse
     * @throws Exception
     */
    public function options(SearchRequest $request, EnrollmentObjectRepositoryInterface $enrollment_object_repository): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $enrollment_object_repository->getOptions($request));
    }

    /**
     * @param EnrollmentObjectRepositoryInterface $enrollment_object_repository
     * @param int $id
     * @return JsonResponse
     */
    public function show(EnrollmentObjectRepositoryInterface $enrollment_object_repository, int $id): JsonResponse
    {
        return json_response(true, $enrollment_object_repository->getById($id));
    }

    /**
     * @param EnrollmentObjectRepositoryInterface $enrollment_object_repository
     * @param CreateEnrollmentObjectRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(EnrollmentObjectRepositoryInterface $enrollment_object_repository, CreateEnrollmentObjectRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $enrollment_object_repository->create($request));
    }

    /**
     * @param EnrollmentObjectRepositoryInterface $enrollment_object_repository
     * @param CreateEnrollmentObjectRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(EnrollmentObjectRepositoryInterface $enrollment_object_repository, CreateEnrollmentObjectRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $enrollment_object_repository->update($id, $request));
    }

    /**
     * @param EnrollmentObjectRepositoryInterface $enrollment_object_repository
     * @param int $id
     * @return JsonResponse
     */
    public function delete(EnrollmentObjectRepositoryInterface $enrollment_object_repository, int $id): JsonResponse
    {
        return json_response(true, $enrollment_object_repository->delete($id));
    }
}