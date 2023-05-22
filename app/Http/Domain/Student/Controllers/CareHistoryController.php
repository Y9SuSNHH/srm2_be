<?php

namespace App\Http\Domain\Student\Controllers;

use App\Http\Domain\Student\Repositories\CareHistory\CareHistoryRepositoryInterface;
use App\Http\Domain\Student\Requests\CareHistory\CreateRequest;
use App\Http\Domain\Student\Requests\CareHistory\UpdateRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;
use App\Http\Domain\Student\Requests\CareHistory\SearchRequest;
use App\Http\Domain\Student\Services\CareHistoryService;

/**
 * Class CareHistoryController
 * @package App\Http\Domain\Student\Controllers
 */
class CareHistoryController extends Controller
{
    /**
     * @param SearchRequest $request
     * @param CareHistoryRepositoryInterface $care_history_repository
     * @return JsonResponse
     */
    public function index(SearchRequest $request): JsonResponse
    {
        return json_response(true, app()->service(CareHistoryService::class)->store($request));
    }
    /**
     * @param CareHistoryRepositoryInterface $care_history_repository
     * @param CreateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(CareHistoryRepositoryInterface $care_history_repository, CreateRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $care_history_repository->create($validator));
    }

    /**
     * @param CareHistoryRepositoryInterface $care_history_repository
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(CareHistoryRepositoryInterface $care_history_repository, UpdateRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $care_history_repository->update($id, $validator));
    }
}