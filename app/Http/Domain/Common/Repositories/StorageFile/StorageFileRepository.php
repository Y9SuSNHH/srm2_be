<?php

namespace App\Http\Domain\Common\Repositories\StorageFile;

use App\Eloquent\StorageFile as EloquentStorageFile;
use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\LengthAwarePaginator;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Common\Model\StorageFile\StorageFile as ModelStorageFile;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorageFileRepository implements StorageFileRepositoryInterface
{
    /** @var EloquentStorageFile */
    private $eloquent_model;

    public function __construct()
    {
        $this->eloquent_model = EloquentStorageFile::getModel();
    }

    public function getAll(PaginateSearchRequest $request): LengthAwarePaginator
    {
        // TODO: Implement getAll() method.
    }

    public function getById(int $id): mixed
    {
        // TODO: Implement getById() method.
    }

    /**
     * @param array $attribute
     * @return ModelStorageFile
     */
    public function create(array $attribute): ModelStorageFile
    {
        try {
            /** @var EloquentStorageFile $storage_file */
            $storage_file = $this->eloquent_model->createOrFail($attribute);
            return new ModelStorageFile($storage_file);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    public function update(int $id, array $attribute): mixed
    {
        // TODO: Implement update() method.
    }

    public function delete(int $id): bool
    {
        // TODO: Implement delete() method.
    }
}