<?php

namespace App\Http\Domain\TrainingProgramme\Controllers;

use App\Http\Domain\TrainingProgramme\Repositories\Curriculum\CurriculumRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\Curriculum\SearchRequest;
use App\Http\Domain\TrainingProgramme\Requests\Curriculum\CreateCurriculumRequest;
use App\Http\Domain\TrainingProgramme\Requests\Curriculum\EditCurriculumRequest;
use App\Http\Domain\TrainingProgramme\Services\Curriculum\ListCurriculum;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;

/**
 * Class CurriculumController
 * @package App\Http\Domain\TrainingProgramme\Controllers
 */
class CurriculumController extends Controller
{
    private $curriculum_repository;

    public function __construct(CurriculumRepositoryInterface $curriculum_repository)
    {
        $this->curriculum_repository = $curriculum_repository;
    }
    
    /**
     * @param CurriculumRepositoryInterface $curriculum_repository
     * @return JsonResponse
     */
    public function index(SearchRequest $request,ListCurriculum $get_detail_list): JsonResponse
    {
        $curriculum_list = $this->curriculum_repository->getAll($request);
        $curriculum_detail_list = $get_detail_list->handle($curriculum_list,$this->curriculum_repository);
        return json_response(true, [
            'curriculum_list' => $curriculum_list,
            'curriculum_detail_list' => $curriculum_detail_list
        ]);
    }

    /**
     * @param CurriculumRepositoryInterface $curriculum_repository
     * @param CreateCurriculumRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(CurriculumRepositoryInterface $curriculum_repository,CreateCurriculumRequest $request)
    {
        $request->throwJsonIfFailed();
        $create_process = $curriculum_repository->create($request->all(),$this->curriculum_repository);
        return json_response($create_process['status'], $create_process['message']);
    }

    /**
     * @param CurriculumRepositoryInterface $curriculum_repository
     * @param EditCurriculumRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(EditCurriculumRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->curriculum_repository->update($id,$request));
    }
    
    /**
     * delete
     *
     * @param  mixed $curriculum_repository
     * @param  mixed $id
     * @param  mixed $request
     * @return JsonResponse
     */
    public function delete(CurriculumRepositoryInterface $curriculum_repository, $id): JsonResponse
    {
        return json_response(true, $curriculum_repository->delete($id));
    }
}