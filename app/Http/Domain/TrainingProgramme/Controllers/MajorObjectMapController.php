<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Http\Domain\TrainingProgramme\Repositories\MajorObjectMap\MajorObjectMapRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\MajorObjectMap\CreateMajorObjectMapRequest;
use App\Http\Domain\TrainingProgramme\Requests\MajorObjectMap\UpdateMajorObjectMapRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;
use App\Http\Domain\TrainingProgramme\Requests\MajorObjectMap\SearchRequest;

/**
 * Class MajorObjectMapController
 * @package App\Http\Domain\TrainingProgramme\Controllers
 */
class MajorObjectMapController extends Controller
{
    /**
     * @param MajorObjectMapRepositoryInterface $major_object_map_repository
     * @return JsonResponse
     */
    public function index(SearchRequest $request, MajorObjectMapRepositoryInterface $major_object_map_repository): JsonResponse
    {
        return json_response(true, $major_object_map_repository->getAll($request));
    }

    /**
     * @param SearchRequest $request
     * @param MajorObjectMapRepositoryInterface $major_object_map_repository
     * @return JsonResponse
     */
    public function options(SearchRequest $request, MajorObjectMapRepositoryInterface $major_object_map_repository): JsonResponse
    {
        return json_response(true, $major_object_map_repository->options($request));
    }

    /**
     * @param MajorObjectMapRepositoryInterface $major_object_map_repository
     * @param int $id
     * @return JsonResponse
     */
    public function show(MajorObjectMapRepositoryInterface $major_object_map_repository, int $id): JsonResponse
    {
        return json_response(true, $major_object_map_repository->getById($id));
    }

    /**
     * @param MajorObjectMapRepositoryInterface $major_object_map_repository
     * @param CreateMajorObjectMapRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(MajorObjectMapRepositoryInterface $major_object_map_repository, CreateMajorObjectMapRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $major_object_map_repository->create($validator));
    }

    // /**
    //  * @param MajorObjectMapRepositoryInterface $major_object_map_repository
    //  * @param UpdateMajorObjectMapRequest $request
    //  * @param int $id
    //  * @return JsonResponse
    //  * @throws Exception
    //  */
    // public function update(MajorObjectMapRepositoryInterface $major_object_map_repository, UpdateMajorObjectMapRequest $request, int $id): JsonResponse
    // {
    //     $request->throwJsonIfFailed();
    //     $validator = $request->all();
    //     return json_response(true, $major_object_map_repository->update($id, $validator));
    // }

    // /**
    //  * @param MajorObjectMapRepositoryInterface $major_object_map_repository
    //  * @param int $id
    //  * @return JsonResponse
    //  */
    // public function delete(MajorObjectMapRepositoryInterface $major_object_map_repository, int $id): JsonResponse
    // {
    //     return json_response(true, $major_object_map_repository->delete($id));
    // }
}