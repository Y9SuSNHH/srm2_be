<?php

namespace App\Http\Domain\Api\Repositories\StorageFile;

use App\Eloquent\StorageFile;
use App\Http\Domain\Api\Models\StorageFile as StorageFileModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class StorageFileRepository implements StorageFileRepositoryInterface
{
    private Builder $query;
    private string $model;

    public function __construct()
    {
        $this->model = StorageFile::class;
        $this->query = StorageFile::query();
    }


    /**
     * @param int $id
     * @return StorageFileModel
     */
    public function getById(int $id): StorageFileModel
    {
        $data = $this->query->clone()->findOrFail($id);
        return new StorageFileModel($data);
    }
}