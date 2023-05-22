<?php

namespace App\Http\Domain\Student\Controllers;

use App\Eloquent\Petition;
use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\Student\Repositories\Petition\PetitionRepositoryInterface;
use App\Http\Domain\Student\Repositories\PetitionFlow\PetitionFlowRepositoryInterface;
use App\Http\Domain\Student\Requests\Petition\SearchRequest;
use App\Http\Domain\Student\Requests\Petition\StoreRequest;
use App\Http\Domain\Student\Requests\Petition\UpdateRequest;
use App\Http\Domain\Student\Services\PetitionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;
use ReflectionException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PetitionController extends Controller
{
    use StepByStep, FileDownloadAble;

    private PetitionService $petition_service;

    public function __construct(PetitionService $petition_service)
    {
        $this->petition_service = $petition_service;
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->petition_service->getAll($request));
    }

    /**
     * @param int $id
     * @param PetitionRepositoryInterface $petition_repository
     * @return JsonResponse
     * @throws ReflectionException
     */
    public function show(int $id, PetitionRepositoryInterface $petition_repository): JsonResponse
    {
        return json_response(true, $petition_repository->getById($id));
    }

    /**
     * @param StoreRequest $request
     * @param $id
     * @return JsonResponse
     * @throws Exception
     */
    public function store(StoreRequest $request, $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->petition_service->add($request, $id));
    }

    /**
     * @param UpdateRequest $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function update(UpdateRequest $request, $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->petition_service->update($request, $id));
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return json_response(true, $this->petition_service->delete($id));
    }

    /**
     * @return JsonResponse
     */
    public function exportInit(): JsonResponse
    {
        $this->initializationStep(['initToken']);
        $token = token_download_generate(30);
        $this->passStep('initToken');
        return json_response(true, ['token' => $token]);
    }


    /**
     * @param SearchRequest $request
     * @return BinaryFileResponse
     * @throws ValidationException
     * @throws ReflectionException
     */
    public function export(SearchRequest $request): BinaryFileResponse
    {
        $this->passesStepOrFail('initToken');
        return $this->createDownloadCsvUTF8BOM($this->petition_service->export($request), "don_tu.csv");
    }
}