<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Controllers;

use App\Helpers\Traits\StepByStep;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom\ClassRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Grade\GradeRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Grade\GradeRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Grade\ImportRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Grade\SearchRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom\SearchRequest as ClassroomSearchRequest;
use App\Http\Domain\AcademicAffairsOfficer\Services\StudentGradeService;
use App\Http\Domain\Common\Model\StorageFile\StorageFile as ModelStorageFile;
use App\Http\Domain\Common\Services\StorageFileService;
use App\Http\Domain\TrainingProgramme\Services\LearningModuleService;
use App\Http\Enum\FileDiv;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;

class GradeController extends Controller
{
    use StepByStep;

    /** @var GradeRepository */
    private $repository;
    /** @var ClassRepositoryInterface */
    private $class_repository;

    public function __construct(GradeRepositoryInterface $grade_repository, ClassRepositoryInterface $class_repository)
    {
        $this->repository = $grade_repository;
        $this->class_repository = $class_repository;
//        app()->register(RepositoryServiceProvider::class);
    }

    /**
     * @param ClassroomSearchRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function getClassroom(ClassroomSearchRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->class_repository->options($request, ['id', 'code']));
    }

    /**
     * @param SearchRequest $request
     * @param StudentGradeService $service
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(SearchRequest $request, StudentGradeService $service): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        [$student_grades] = $service->getStudentGrade($this->repository, $request);

        return json_response(true, [
            'studentGrades' => $student_grades,
            'learningModuleId' => null,
//            'gradeDiv' => $grade_div,
        ]);
    }

    /**
     * @param StudentGradeService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function importInit(StudentGradeService $service): \Illuminate\Http\JsonResponse
    {
        /** @var LearningModuleService $learning_module_service */
        $learning_module_service = app()->service(LearningModuleService::class);
        $learning_module = $learning_module_service->getOptions();
        $this->initializationStep(['init', 'validate']);
        $this->setData([]);
        $this->passStep('init');
        return json_response(true, $learning_module);
    }

    /**
     * @param ImportRequest $request
     * @param StorageFileService $storage_file_service
     * @param StudentGradeService $service
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function importValidate(ImportRequest $request, StorageFileService $storage_file_service, StudentGradeService $service): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();

        if ($this->checkPassesStep('init')) {
            [$errors, $preview, $data, $grade_values] = $service->analyzing($request, $this->repository);

            if (!empty($errors)) {
                return json_response(true, ['errors' => $errors, 'data' => null]);
            }

            $model_storage_file = $storage_file_service->putFileToTempStorage($request->file, FileDiv::GRADE_IMPORT);
            $this->passStep('validate');
            $this->setData([
                'file' => $model_storage_file->toStandardArray(),
                'grades' => $data,
                'grade_values' => $grade_values,
            ]);

            return json_response(true, ['errors' => null, 'data' => $preview]);
        }

        return json_response(false);
    }

    /**
     * @param ImportRequest $request
     * @param StorageFileService $storage_file_service
     * @param StudentGradeService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function importStore(ImportRequest $request, StorageFileService $storage_file_service, StudentGradeService $service): \Illuminate\Http\JsonResponse
    {
        if ($this->checkPassesStep('validate')) {
            $data = $this->getData();
            $file   = $storage_file_service->saveFileInStorage(new ModelStorageFile($data['file']));
            $service->store($file->id, $data['grades'], $data['grade_values'], $this->repository);
            return json_response(true);
        }

        return json_response(false);
    }

    /**
     * @param int $storage_file_id
     * @param StudentGradeService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteInit(int $storage_file_id, StudentGradeService $service): \Illuminate\Http\JsonResponse
    {
        [$count, $grade_ids] = $service->getGradeDeleted($this->repository, $storage_file_id);

        if (!$count) {
            return json_response(false, ['count' => 0], 'Không có bản ghi nào được xóa');
        }

        $this->initializationStep(['init-delete']);
        $this->setData($grade_ids);
        $this->passStep('init-delete');
        return json_response(true, compact('count', 'grade_ids'));
    }

    /**
     * @param int $storage_file_id
     * @param StudentGradeService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(int $storage_file_id, StudentGradeService $service): \Illuminate\Http\JsonResponse
    {
        if ($this->checkPassesStep('init-delete')) {
            $grade_ids = $this->getData();
            $result = $service->delete($this->repository, $storage_file_id, $grade_ids);

            if ($result) {
                return json_response(true);
            }
        }

        return json_response(false);
    }
}
