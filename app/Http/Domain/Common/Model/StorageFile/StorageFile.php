<?php

namespace App\Http\Domain\Common\Model\StorageFile;

use App\Helpers\Json;
use App\Helpers\Traits\CamelArrayAble;
use App\Http\Enum\FileDiv;
use App\Http\Enum\StorageDiv;

/**
 * Class StorageFile
 * @package App\Http\Domain\Common\Model\StorageFile
 *
 * @property $id
 * @property $storage_div
 * @property $file_path
 * @property $file_type
 * @property $file_size
 * @property $origin_name
 * @property $file_div
 * @property $uploader
 * @property $file_url
 */
class StorageFile extends Json
{
    use CamelArrayAble;

    public $id;
    public $storage_div;
    public $file_path;
    public $file_type;
    public $file_size;
    public $origin_name;
    public $file_div;
    public $uploader;
    public $file_url;

    /**
     * @return array
     */
    public function toStandardArray(): array
    {
        return [
            'storage_div' => $this->storage_div,
            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'origin_name' => $this->origin_name,
            'file_div' => $this->file_div,
            'uploader' => $this->uploader,
            'file_url' => $this->file_url,
        ];
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getStorageDivName(): string
    {
        return StorageDiv::from($this->storage_div)->getKey();
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getFileDivName(): string
    {
        return FileDiv::from($this->file_div)->getKey();
    }
}
