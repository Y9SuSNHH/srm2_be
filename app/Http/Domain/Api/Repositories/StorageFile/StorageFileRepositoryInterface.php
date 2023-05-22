<?php

namespace App\Http\Domain\Api\Repositories\StorageFile;

use App\Http\Domain\Api\Models\StorageFile as StorageFileModel;

interface StorageFileRepositoryInterface
{
    /**
     * @param int $id
     * @return StorageFileModel
     */
    public function getById(int $id): StorageFileModel;
}