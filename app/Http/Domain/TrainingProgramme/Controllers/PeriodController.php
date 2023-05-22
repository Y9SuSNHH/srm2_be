<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Http\Domain\TrainingProgramme\Repositories\Period\PeriodsRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;
use App\Http\Domain\TrainingProgramme\Requests\Period\SearchRequest;

/**
 * Class PeriodController
 * @package App\Http\Domain\TrainingProgramme\Controllers
 */
class PeriodController extends Controller
{
    /**
     * @param PeriodsRepositoryInterface $period_repository
     * @return JsonResponse
     */
    public function index(SearchRequest $request, PeriodsRepositoryInterface $period_repository): JsonResponse
    {
        return json_response(true, $period_repository->getAll($request));
    }

    /**
     * @param SearchRequest $request
     * @param PeriodsRepositoryInterface $period_repository
     * @return JsonResponse
     */
    public function options(SearchRequest $request, PeriodsRepositoryInterface $period_repository): JsonResponse
    {
        return json_response(true, $period_repository->options($request));
    }

    /**
     * @param PeriodsRepositoryInterface $period_repository
     * @param int $id
     * @return JsonResponse
     */
    public function delete(PeriodsRepositoryInterface $period_repository, int $id): JsonResponse
    {
        return json_response(true, $period_repository->delete($id));
    }
}
