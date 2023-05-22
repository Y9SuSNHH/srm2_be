<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Http\Domain\TrainingProgramme\Repositories\Subject\SubjectRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\Subject\CreateSubjectRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;
use App\Http\Domain\TrainingProgramme\Requests\Subject\SearchRequest;

/**
 * Class SubjectController
 * @package App\Http\Domain\TrainingProgramme\Controllers
 */
class SubjectController extends Controller
{
    /**
     * @param SubjectRepositoryInterface $subject
     * @return JsonResponse
     */
    public function index(SearchRequest $request, SubjectRepositoryInterface $subject): JsonResponse
    {
        return json_response(true, $subject->getAll($request));
    }

    /**
     * @param SearchRequest $request
     * @param SubjectRepositoryInterface $subject
     * @return JsonResponse
     */
    public function options(SearchRequest $request, SubjectRepositoryInterface $subject): JsonResponse
    {
        return json_response(true, $subject->options($request));
    }

    /**
     * @param SubjectRepositoryInterface $subject
     * @param int $id
     * @return JsonResponse
     */
    public function show(SubjectRepositoryInterface $subject, int $id): JsonResponse
    {
        return json_response(true, $subject->getById($id));
    }

    /**
     * @param SubjectRepositoryInterface $subject
     * @param CreateSubjectRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(SubjectRepositoryInterface $subject, CreateSubjectRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $subject->create($validator));
    }

    /**
     * @param SubjectRepositoryInterface $subject
     * @param CreateSubjectRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(SubjectRepositoryInterface $subject, CreateSubjectRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $subject->update($id, $validator));
    }

    /**
     * @param SubjectRepositoryInterface $subject
     * @param int $id
     * @return JsonResponse
     */
    public function delete(SubjectRepositoryInterface $subject, int $id): JsonResponse
    {
        return json_response(true, $subject->delete($id));
    }
}