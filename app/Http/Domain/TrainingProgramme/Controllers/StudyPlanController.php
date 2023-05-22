<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\TrainingProgramme\Repositories\StudyPlan\StudyPlanRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\StudyPlan\CreateStudyPlanRequest;
use App\Http\Domain\TrainingProgramme\Requests\StudyPlan\UpdateStudyPlanRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;
use App\Http\Domain\TrainingProgramme\Requests\StudyPlan\SearchRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Domain\TrainingProgramme\Services\StudyPlanService;
use App\Providers\AuthManager;

/**
 * Class StudyPlanController
 * @package App\Http\Domain\TrainingProgramme\Controllers
 */
class StudyPlanController extends Controller
{
    use StepByStep, FileDownloadAble;

    /**
     * @param SearchRequest $request
     * @param StudyPlanService $study_plan_service
     * @return JsonResponse
     * @throws Exception
     */
    public function index(SearchRequest $request, StudyPlanService $study_plan_service): JsonResponse
    {
        $request->throwJsonIfFailed();
        $study_plan = $study_plan_service->getList($request);
        return json_response(true, $study_plan);
    }

     /**
     * @param SearchRequest $request
     * @param StudyPlanRepositoryInterface $study_plan_repository
     * @return JsonResponse
     */
    public function options(SearchRequest $request, StudyPlanRepositoryInterface $study_plan_repository): JsonResponse
    {
        return json_response(true, $study_plan_repository->options($request));
    }

    /**
     * @param StudyPlanRepositoryInterface $study_plan_repository
     * @param int $id
     * @return JsonResponse
     */
    public function show(StudyPlanRepositoryInterface $study_plan_repository, int $id): JsonResponse
    {
        return json_response(true, $study_plan_repository->getById($id));
    }

    /**
     * @param StudyPlanRepositoryInterface $study_plan_repository
     * @param CreateStudyPlanRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(StudyPlanRepositoryInterface $study_plan_repository, CreateStudyPlanRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $study_plan_repository->create($validator));
    }

    /**
     * @param StudyPlanRepositoryInterface $study_plan_repository
     * @param UpdateStudyPlanRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(StudyPlanRepositoryInterface $study_plan_repository, UpdateStudyPlanRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $study_plan_repository->update($id, $request->validated()));
    }

    /**
     * @param StudyPlanRepositoryInterface $study_plan_repository
     * @param int $id
     * @return JsonResponse
     */
    public function delete(StudyPlanRepositoryInterface $study_plan_repository, int $id): JsonResponse
    {
        return json_response(true, $study_plan_repository->delete($id));
    }
    
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function downloadInit(): \Illuminate\Http\JsonResponse
    {
        $this->initializationStep(['initToken']);
        $token = token_download_generate(30);
        $this->passStep('initToken');
        return json_response(true, ['token' => $token]);
    }

    /**
     * @param StudyPlanService $service
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTemplate(StudyPlanService $service, SearchRequest $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->passesStepOrFail('initToken');
        return $this->createDownloadCsvUTF8BOM($service->createTemplateFile($request), "KHHT.csv");
    }

    public function sendParams(): JsonResponse
    {
        return json_response(true, app()->service(StudyPlanService::class)->getParams());
    }
}