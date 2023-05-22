<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Http\Domain\TrainingProgramme\Repositories\Major\MajorRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\Major\CreateMajorRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;

/**
 * Class MajorController
 * @package App\Http\Domain\TrainingProgramme\Controllers
 */
class MajorController extends Controller
{
    /**
     * @param MajorRepositoryInterface $major_repository
     * @return JsonResponse
     */
    public function index(MajorRepositoryInterface $major_repository): JsonResponse
    {
        return json_response(true, $major_repository->getAll());
    }
    /**
     * @param MajorRepositoryInterface $major_repository
     * @return JsonResponse
     * @throws Exception
     */
    public function options(MajorRepositoryInterface $major_repository): JsonResponse
    {
        return json_response(true, $major_repository->getAll());
    }

    /**
     * @param MajorRepositoryInterface $major_repository
     * @param int $id
     * @return JsonResponse
     */
    public function show(MajorRepositoryInterface $major_repository, int $id): JsonResponse
    {
        return json_response(true, $major_repository->getById($id));
    }

    /**
     * @param MajorRepositoryInterface $major_repository
     * @param CreateMajorRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(MajorRepositoryInterface $major_repository, CreateMajorRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $major_repository->create($request));
    }

    /**
     * @param MajorRepositoryInterface $major_repository
     * @param CreateMajorRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(MajorRepositoryInterface $major_repository, CreateMajorRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $major_repository->update($id, $request));
    }

    /**
     * @param MajorRepositoryInterface $major_repository
     * @param int $id
     * @return JsonResponse
     */
    public function delete(MajorRepositoryInterface $major_repository, int $id): JsonResponse
    {
        return json_response(true, $major_repository->delete($id));
    }
}