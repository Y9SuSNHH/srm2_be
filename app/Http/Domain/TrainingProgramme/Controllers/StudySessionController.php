<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use App\Http\Domain\TrainingProgramme\Repositories\StudySession\PeriodRepository;
use App\Http\Domain\TrainingProgramme\Repositories\StudySession\PeriodRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\StudySession\UploadPeriodRequest;
use App\Http\Domain\TrainingProgramme\Services\PeriodService;
use App\Http\Domain\TrainingProgramme\Services\StudySessionService;
use App\Providers\AuthManager;

class StudySessionController
{
    use StepByStep, FileDownloadAble;

    /** @var PeriodRepository */
    private $repository;
    /** @var StudySessionService */
    private $service;

    public function __construct(PeriodRepositoryInterface $period_repository)
    {
        $this->repository = $period_repository;
        $this->service = new StudySessionService($this->repository);
    }

    /**
     * @param BaseSearchRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(BaseSearchRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->repository->getAll($request));
    }

    public function show(int $id)
    {

    }

    public function update()
    {

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
     * @param PeriodService $service
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTemplate(PeriodService $service): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->passesStepOrFail('initToken');
        return $this->createDownloadCsvUTF8BOM($service->createTemplateFile(), "Dot_hoc.csv");
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadInit()
    {
        $this->initializationStep(['initForm', 'validator']);
        $this->passStep('initForm');
        return json_response(true, ['passed' => 'initForm']);
    }

    /**
     * @param UploadPeriodRequest $request
     * @param PeriodService $service
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function uploadValidate(UploadPeriodRequest $request, PeriodService $service): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        $this->passesStepOrFail($request->passed);
        [$errors, $preview, $data] = $service->analyzing($request, $this->repository);

        if (empty($errors)) {
            $this->passStep('validator');
            $this->setData($data);
            return json_response(true, ['passed' => 'validator', 'data' => $preview]);
        }

        return json_response(true, ['passed' => 'initForm', 'errors' => $errors]);
    }

    /**
     * @param PeriodService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadStore(PeriodService $service): \Illuminate\Http\JsonResponse
    {
        if ($this->checkPassesStep('validator')) {
            $data = $this->getData();

            if ($service->store($this->repository, $data)) {
                return json_response(true);
            }
        }

        return json_response(false, [], 'import fail');
    }
}
