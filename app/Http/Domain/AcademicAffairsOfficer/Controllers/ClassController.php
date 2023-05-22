<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Controllers;

use App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom\ClassRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom\CreateBatchRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom\CreateRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom\SearchRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom\UpdateLearningManagementRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom\UpdateRequest;
use App\Http\Domain\AcademicAffairsOfficer\Services\ClassService;
use App\Http\Domain\TrainingProgramme\Services\MajorObjectMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;

/**
 * Class ClassController
 * @package App\Http\Domain\AcademicAffairsOfficer\Controllers
 */
class ClassController extends Controller
{
    private $repository;

    /**
     * ClassController constructor.
     * @param ClassRepositoryInterface $class_repository
     */
    public function __construct(ClassRepositoryInterface $class_repository)
    {
        $this->repository = $class_repository;
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->repository->getAll($request));
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function options(SearchRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->repository->options($request));
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return json_response(true, $this->repository->getById($id));
    }

    /**
     * @param CreateRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->repository->create($request->validated()));
    }

    /**
     * @param int $id
     * @param UpdateRequest $request
     * @param ClassRepositoryInterface $class_repository
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function update(int $id, UpdateRequest $request, ClassRepositoryInterface $class_repository): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->repository->update($id, $request->validated()));
    }

    /**
     * @param int $id
     * @param ClassRepositoryInterface $class_repository
     * @return JsonResponse
     */
    public function delete(int $id, ClassRepositoryInterface $class_repository): JsonResponse
    {
        return json_response($class_repository->delete($id));
    }

    /**
     * @param ClassService $service
     * @param CreateBatchRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function createBatch(ClassService $service, CreateBatchRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        $result = $service->createMultiple($request);

        if ($result) {
            return json_response(true, $result);
        }

        return json_response(false, [], 'Fail');
    }

    /**
     * @param UpdateLearningManagementRequest $update_learning_management_request
     * @param ClassService $service
     * @return JsonResponse
     * @throws \Exception
     */
    public function updateLearningManagement(UpdateLearningManagementRequest $update_learning_management_request, ClassService $service): JsonResponse
    {
        $update_learning_management_request->throwJsonIfFailed();
        return json_response($service->assignLearningManagement($update_learning_management_request));
    }

    /**
     * @return JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getMajorAndObject(): JsonResponse
    {
        /** @var MajorObjectMapService $service */
        $service = app()->service(MajorObjectMapService::class);
        return json_response(true, $service->getMajorAndObject(request()->get('area_id')));
    }
}
