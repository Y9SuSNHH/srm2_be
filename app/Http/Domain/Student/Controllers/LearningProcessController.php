<?php

namespace App\Http\Domain\Student\Controllers;

use App\Http\Domain\Student\Repositories\LearningProcess\LearningProcessRepository;
use App\Http\Domain\Student\Repositories\LearningProcess\LearningProcessRepositoryInterface;
use App\Http\Domain\Student\Services\LearningProcessService;
use App\Http\Domain\Student\Requests\LearningProcess\SearchRequest;
use App\Http\Domain\Student\Requests\LearningProcess\CreateRequest;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * Class LearningProcessController
 * @package App\Http\Domain\Student\Controllers
 */
class LearningProcessController
{
    /** @var LearningProcessRepository */
    private $learning_process_repository;
    private $learning_process_service;

    public function __construct(LearningProcessRepositoryInterface $repository, LearningProcessService $service)
    {
        $this->learning_process_repository = $repository;
        $this->learning_process_service = $service;
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     */
    public function index(SearchRequest $request): JsonResponse
    {
        return json_response(true, $this->learning_process_service->listLearningProcess($request));
    }

    public function test()
    {
        return json_response(true, $this->learning_process_service->store());
    }

    /**
     * @param LearningProcessRepositoryInterface $learning_process_repository
     * @param CreateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $this->learning_process_repository->create($validator));
    }
}
