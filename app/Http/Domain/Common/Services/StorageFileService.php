<?php

namespace App\Http\Domain\Common\Services;

use App\Http\Domain\Common\Model\StorageFile\StorageFile as ModelStorageFile;
use App\Http\Domain\Common\Repositories\StorageFile\StorageFileRepository;
use App\Http\Domain\Common\Repositories\StorageFile\StorageFileRepositoryInterface;
use App\Http\Enum\StorageDiv;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class StorageFileService
{
    public const TEMP_UPLOAD_PATH = 'tmp';
    public const UPLOAD_PATH = 'files';

    /**
     * @param UploadedFile $file
     * @param int $file_div
     * @return ModelStorageFile
     * @throws \Exception
     */
    public function putFileToTempStorage(UploadedFile $file, int $file_div): ModelStorageFile
    {
        $extension = $file->getClientOriginalExtension();
        $filename = sprintf('%s-%s.%s', Uuid::uuid4(), Carbon::now()->format('YmdHis'), strtolower($extension));
        $result = Storage::putFileAs(self::TEMP_UPLOAD_PATH, $file, $filename);

        if (!$result) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => 'failed to save uploaded file to temp folder']));
        }

        return new ModelStorageFile([
            'file_path' => self::TEMP_UPLOAD_PATH .'/'. $filename,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'origin_name' => $file->getClientOriginalName(),
            'file_div' => $file_div,
            'uploader' => auth()->getId(),
        ]);
    }

    /**
     * @param ModelStorageFile $storage_file
     * @return ModelStorageFile
     */
    public function saveFileInStorage(ModelStorageFile $storage_file): ModelStorageFile
    {
        $extension = substr($storage_file->file_path, strrpos($storage_file->file_path, '.'));
        $file_path = sprintf('%s/%s_%s_%s.%s', self::UPLOAD_PATH, str_replace('-', '_', Uuid::uuid4()), school()->getId(), Carbon::now()->format('YmdHis'), strtolower($extension));

        try {
            if (!Storage::disk()->exists($storage_file->file_path)) {
                throw new \Exception('the original file in the temp directory is not exist');
            }

            if (!Storage::disk()->move($storage_file->file_path, $file_path)) {
                throw new \Exception('failed to save uploaded file');
            }
        } catch (\Exception $exception) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $exception->getMessage()]));
        }

        /** @var StorageFileRepository $storage_file_repository */
        $storage_file_repository = app(StorageFileRepositoryInterface::class);

        return $storage_file_repository->create([
            'storage_div' => StorageDiv::LOCAL,
            'file_path' => $file_path,
            'file_type' => $storage_file->file_type,
            'file_size' => $storage_file->file_size,
            'origin_name' => $storage_file->origin_name,
            'file_div' => $storage_file->file_div,
            'uploader' => $storage_file->uploader,
            'file_url' => $storage_file->file_url,
        ]);
    }
}
