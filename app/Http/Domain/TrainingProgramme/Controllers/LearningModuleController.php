<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Http\Domain\TrainingProgramme\Repositories\LearningModule\LearningModuleRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\LearningModule\CreateLearningModuleRequest;
use App\Http\Domain\TrainingProgramme\Requests\LearningModule\UpdateLearningModuleRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;
use App\Http\Domain\TrainingProgramme\Requests\LearningModule\SearchRequest;

/**
 * Class LearningModuleController
 * @package App\Http\Domain\TrainingProgramme\Controllers
 */
class LearningModuleController extends Controller
{
    /**
     * @param SearchRequest $request
     * @param LearningModuleRepositoryInterface $learning_module_repository
     * @return JsonResponse
     */
    public function index(SearchRequest $request, LearningModuleRepositoryInterface $learning_module_repository): JsonResponse
    {
        return json_response(true, $learning_module_repository->getAll($request));
    }

    /**
     * @param SearchRequest $request
     * @param LearningModuleRepositoryInterface $learning_module_repository
     * @return JsonResponse
     */
    public function options(SearchRequest $request, LearningModuleRepositoryInterface $learning_module_repository): JsonResponse
    {
        return json_response(true, $learning_module_repository->options($request));
    }

    /**
     * @param LearningModuleRepositoryInterface $learning_module_repository
     * @param int $id
     * @return JsonResponse
     */
    public function show(LearningModuleRepositoryInterface $learning_module_repository, int $id): JsonResponse
    {
        return json_response(true, $learning_module_repository->getById($id));
    }

    /**
     * @param LearningModuleRepositoryInterface $learning_module_repository
     * @param CreateLearningModuleRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(LearningModuleRepositoryInterface $learning_module_repository, CreateLearningModuleRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $learning_module_repository->create($validator));
    }

    /**
     * @param LearningModuleRepositoryInterface $learning_module_repository
     * @param UpdateLearningModuleRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(LearningModuleRepositoryInterface $learning_module_repository, UpdateLearningModuleRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $learning_module_repository->update($id, $validator));
    }

    /**
     * @param LearningModuleRepositoryInterface $learning_module_repository
     * @param int $id
     * @return JsonResponse
     */
    public function delete(LearningModuleRepositoryInterface $learning_module_repository, int $id): JsonResponse
    {
        return json_response(true, $learning_module_repository->delete($id));
    }
}