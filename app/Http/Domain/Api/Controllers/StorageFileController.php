<?php

namespace App\Http\Domain\Api\Controllers;

use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\Api\Repositories\StorageFile\StorageFileRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageFileController
{
    use StepByStep, FileDownloadAble;

    private StorageFileRepositoryInterface $repository;
    public const DOWNLOAD_INIT = 'downloadInit';

    public function __construct(StorageFileRepositoryInterface $storage_file_repository)
    {
        $this->repository = $storage_file_repository;
    }


    /**
     * @return JsonResponse
     */
    public function downloadInit(): JsonResponse
    {
        $this->initializationStep([self::DOWNLOAD_INIT]);
        $token = token_download_generate(30);
        $this->passStep(self::DOWNLOAD_INIT);
        return json_response(true, ['token' => $token]);
    }


    /**
     * @param int $id
     * @return StreamedResponse
     */
    public function download(int $id): StreamedResponse
    {
        $this->passesStepOrFail(self::DOWNLOAD_INIT);
        $storage_file = $this->repository->getById($id);
        return Storage::download($storage_file->file_path, $storage_file->origin_name);
    }
}