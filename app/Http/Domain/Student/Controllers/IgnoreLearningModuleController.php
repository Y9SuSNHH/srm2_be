<?php

namespace App\Http\Domain\Student\Controllers;

use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\Common\Model\StorageFile\StorageFile as ModelStorageFile;
use App\Http\Domain\Common\Services\StorageFileService;
use App\Http\Domain\Student\Repositories\IgnoreLearningModule\IgnoreLearningModuleRepositoryInterface;
use App\Http\Domain\Student\Repositories\LearningModule\LearningModuleRepositoryInterface;
use App\Http\Domain\Student\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Student\Requests\IgnoreLearningModule\ImportRequest;
use App\Http\Domain\Student\Requests\IgnoreLearningModule\SearchRequest;
use App\Http\Domain\Student\Requests\IgnoreLearningModule\StoreRequest;
use App\Http\Domain\Student\Services\IgnoreLearningModuleService;
use App\Http\Enum\FileDiv;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;
use ReflectionException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IgnoreLearningModuleController extends Controller
{
    use StepByStep, FileDownloadAble;

    private IgnoreLearningModuleService $service;
    private IgnoreLearningModuleRepositoryInterface $repository;

    private const IMPORT_INIT          = 'importInit';
    private const IMPORT_VALIDATOR     = 'importValidator';
    private const IMPORT_TEMPLATE_INIT = 'importTemplateInit';
    private const EXPORT_INIT          = 'exportInit';

    public function __construct(IgnoreLearningModuleService $ignore_learning_module_service, IgnoreLearningModuleRepositoryInterface $ignore_learning_module_repository)
    {
        $this->service    = $ignore_learning_module_service;
        $this->repository = $ignore_learning_module_repository;
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->repository->getAll($request));
    }


    /**
     * @param int $id
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function store(int $id, StoreRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->service->add($id, $request));
    }


    /**
     * @return JsonResponse
     */
    public function importInit(): JsonResponse
    {
        $this->initializationStep([self::IMPORT_INIT, self::IMPORT_VALIDATOR]);
        $this->passStep(self::IMPORT_INIT);
        return json_response(true, ['passed' => self::IMPORT_INIT]);
    }


    /**
     * @param ImportRequest $request
     * @param StorageFileService $storage_file_service
     * @param StudentRepositoryInterface $student_repository
     * @param LearningModuleRepositoryInterface $learning_module_repository
     * @return JsonResponse
     * @throws Exception
     */
    public function importValidator(ImportRequest $request, StorageFileService $storage_file_service, StudentRepositoryInterface $student_repository, LearningModuleRepositoryInterface $learning_module_repository): JsonResponse
    {
        $request->throwJsonIfFailed();
        $this->passesStepOrFail($request->passed);

        [$errors, $preview, $data] = $this->service->importValidator($request, $student_repository, $learning_module_repository);
        if (!empty($errors)) {
            return json_response(true, [
                'errors' => count($errors),
                'passed' => self::IMPORT_INIT,
                'data'   => $preview
            ]);
        }

        $model_storage_file = $storage_file_service->putFileToTempStorage($request->file, FileDiv::IGNORE_LEARNING_MODULE_IMPORT);
        $this->passStep(self::IMPORT_VALIDATOR);
        $this->setData([
            'file' => $model_storage_file->toStandardArray(),
            'data' => $data,
        ]);
        return json_response(true, [
            'errors' => null,
            'passed' => self::IMPORT_VALIDATOR,
            'data'   => $preview
        ]);
    }


    /**
     * @param ImportRequest $request
     * @param StorageFileService $storage_file_service
     * @return JsonResponse
     * @throws ReflectionException
     */
    public function import(ImportRequest $request, StorageFileService $storage_file_service): JsonResponse
    {
        if ($this->checkPassesStep(self::IMPORT_VALIDATOR)) {
            $data = $this->getData();

            $file   = $storage_file_service->saveFileInStorage(new ModelStorageFile($data['file']));
            $insert = [];
            foreach ($data['data'] as $each) {
                $insert[] = [
                    'created_by'         => auth()->getId(),
                    'student_id'         => $each['student_id'],
                    'learning_module_id' => $each['learning_module_id'],
                    'storage_file_id'    => $file->id,
                ];
            }
            $this->repository->insert($insert);

            return json_response(true);
        }
        return json_response(false);
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws ReflectionException
     */
    public function destroy(int $id): JsonResponse
    {
        return json_response(true, $this->repository->deletedById($id));
    }


    /**
     * @return JsonResponse
     */
    public function templateInit(): JsonResponse
    {
        $this->initializationStep([self::IMPORT_TEMPLATE_INIT]);
        $token = token_download_generate(30);
        $this->passStep(self::IMPORT_TEMPLATE_INIT);
        return json_response(true, ['token' => $token]);
    }

    /**
     * @return BinaryFileResponse
     */
    public function templateDownload(): BinaryFileResponse
    {
        $this->passesStepOrFail(self::IMPORT_TEMPLATE_INIT);
        return $this->createDownloadCsvUTF8BOM($this->service->downloadTemplate(), "don_tu_mien_mon.csv");
    }

    /**
     * @return JsonResponse
     */
    public function exportInit(): JsonResponse
    {
        $this->initializationStep([self::EXPORT_INIT]);
        $token = token_download_generate(30);
        $this->passStep(self::EXPORT_INIT);
        return json_response(true, ['token' => $token]);
    }


    public function export(SearchRequest $request)
    {
        $this->passesStepOrFail(self::EXPORT_INIT);
        return $this->createDownloadCsvUTF8BOM($this->service->export($request), "mien_mon.csv");
    }
}