<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Http\Domain\TrainingProgramme\Repositories\EnrollmentWave\EnrollmentWaveRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\EnrollmentWave\CreateEnrollmentWaveRequest;
use App\Http\Domain\TrainingProgramme\Services\EnrollmentWave\ChangeDateFormat;
use App\Http\Domain\TrainingProgramme\Services\EnrollmentWaveService;
use Laravel\Lumen\Routing\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Domain\TrainingProgramme\Requests\EnrollmentWave\SearchRequest;

class EnrollmentWaveController extends Controller
{
    /** @var EnrollmentWaveRepositoryInterface */
    private $enrollment_wave_repository;

    /** @var EnrollmentWaveService */
    private $service;

    /**
     * EnrollmentWaveController constructor.
     * @param EnrollmentWaveRepositoryInterface $repository
     */
    public function __construct(EnrollmentWaveRepositoryInterface $repository)
    {
        $this->enrollment_wave_repository = $repository;
        $this->service = new EnrollmentWaveService($this->enrollment_wave_repository);
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->enrollment_wave_repository->getAll($request));
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     */
    public function options(SearchRequest $request): JsonResponse
    {
        return json_response(true, $this->enrollment_wave_repository->options($request));
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return json_response(true, $this->service->getDetail($id));
    }

    /**
     * @param CreateEnrollmentWaveRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(CreateEnrollmentWaveRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->service->store($request));
    }

    /**
     * @param ChangeDateFormat $changeDateFormat
     * @param CreateEnrollmentWaveRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(ChangeDateFormat $changeDateFormat, CreateEnrollmentWaveRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->service->update($id, $request));
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        return json_response(true, $this->service->delete($id));
    }

}