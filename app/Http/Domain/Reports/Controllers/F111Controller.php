<?php

namespace App\Http\Domain\Reports\Controllers;

use App\Http\Domain\Reports\Repositories\F111\F111RepositoryInterface;
use App\Http\Domain\Reports\Services\F111\F111ExportFileTemplateService;
use App\Http\Domain\Reports\Requests\F111\UploadRequest;
use App\Http\Domain\Reports\Services\F111\F111UploadFileService;
use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\Common\Services\StorageFileService;
use App\Http\Domain\Common\Model\StorageFile\StorageFile as ModelStorageFile;
use App\Http\Enum\FileDiv;
use Laravel\Lumen\Routing\Controller;

/**
 * Class F111Controller
 * @package App\Http\Domain\Reports\Controllers
 */
class F111Controller extends Controller
{
    use StepByStep, FileDownloadAble;
    private $f111_repository;
    private $f111_export_file_template_service;
    private $f111_upload_file_service;

    public function __construct(
        F111RepositoryInterface $f111_repository,
        F111ExportFileTemplateService $f111_export_file_template_service,
        F111UploadFileService $f111_upload_file_service
    ) {
        $this->f111_repository = $f111_repository;
        $this->f111_export_file_template_service = $f111_export_file_template_service;
        $this->f111_upload_file_service = $f111_upload_file_service;
    }

    public function downloadInit(): \Illuminate\Http\JsonResponse
    {
        $this->initializationStep(['initToken']);
        $token = token_download_generate(300);
        $this->passStep('initToken');
        return json_response(true, ['token' => $token]);
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->passesStepOrFail('initToken');
        return $this->createDownloadCsvUTF8BOM($this->f111_export_file_template_service->createTemplateFile(), "F111.csv");
    }

    public function uploadInit()
    {
        $this->initializationStep(['initForm', 'validator']);
        $this->setData([]);
        $this->passStep('initForm');
        return json_response(true, ['passed' => 'initForm']);
    }

    public function uploadValidate(UploadRequest $request, StorageFileService $storage_file_service): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();

        if ($this->checkPassesStep('initForm')) {
            [$errors, $preview, $profiles, $student_profiles, $students, $student_classrooms] = $this->f111_upload_file_service->analyzing($request, $this->f111_repository);

            if (!empty($errors)) {
                return json_response(true, ['errors' => $errors, 'data' => null]);
            }

            $model_storage_file = $storage_file_service->putFileToTempStorage($request->file, FileDiv::STUDENT_PROFILE_IMPORT);
            $this->passStep('validator');
            $this->setData([
                'file' => $model_storage_file->toStandardArray(),
                'profiles' => $profiles,
                'student_profiles' => $student_profiles,
                'students' => $students,
                'student_classrooms' => $student_classrooms,
            ]);

            return json_response(true, ['errors' => null, 'data' => $preview]);
        }

        return json_response(false);

    }

    public function uploadStore(StorageFileService $storage_file_service): \Illuminate\Http\JsonResponse
    {
        if ($this->checkPassesStep('validator')) {
            $data = $this->getData();
            $file = $storage_file_service->saveFileInStorage(new ModelStorageFile($data['file']));
            $this->f111_upload_file_service->store(1, $data['profiles'], $data['student_profiles'], $data['students'], $data['student_classrooms'], $this->f111_repository);
            return json_response(true);
        }

        return json_response(false);
    }
}
