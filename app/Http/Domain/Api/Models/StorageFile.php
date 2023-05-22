<?php

namespace App\Http\Domain\Api\Models;

use App\Helpers\Json;

class StorageFile extends Json
{
    public $id;
    public $file_path;
    public $file_type;
    public $origin_name;
}