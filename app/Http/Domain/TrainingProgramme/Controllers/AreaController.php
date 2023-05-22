<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Http\Domain\TrainingProgramme\Requests\Area\CreateAreaRequest;
use App\Http\Domain\TrainingProgramme\Requests\Area\UpdateAreaRequest;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;
use Exception;
use App\Http\Domain\TrainingProgramme\Repositories\Area\AreaRepositoryInterface;

/**
 * Class AreaController
 * @package App\Http\Domain\TrainingProgramme\Controllers
 */

class AreaController extends Controller
{
    /**
     * @param AreaRepositoryInterface $area_repository
     * @return JsonResponse
     */
    public function index(AreaRepositoryInterface $area_repository): JsonResponse
    {
        return json_response(true, $area_repository->getAll(), []);
    }

    /**
     * @param AreaRepositoryInterface $area_repository
     * @return JsonResponse
     */
    public function options(AreaRepositoryInterface $area_repository): JsonResponse
    {
        return json_response(true, $area_repository->getAll()->pluck('code', 'id'));
    }

    /**
     * @param AreaRepositoryInterface $area_repository
     * @param int $id
     * @return JsonResponse
     */
    public function show(AreaRepositoryInterface $area_repository, int $id): JsonResponse
    {
        return json_response(true, $area_repository->getById($id));
    }

    /**
     * @param AreaRepositoryInterface $area_repository
     * @param CreateAreaRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(AreaRepositoryInterface $area_repository, CreateAreaRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $area_repository->create($request));
    }

    /**
     * @param AreaRepositoryInterface $area_repository
     * @param UpdateAreaRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(AreaRepositoryInterface $area_repository, UpdateAreaRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $area_repository->update($id, $request));
    }

    /**
     * @param AreaRepositoryInterface $area_repository
     * @param int $id
     * @return JsonResponse
     */
    public function delete(AreaRepositoryInterface $area_repository, int $id): JsonResponse
    {
        return json_response(true, $area_repository->delete($id));
    }
}
