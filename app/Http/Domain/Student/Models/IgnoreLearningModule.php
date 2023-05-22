<?php

namespace App\Http\Domain\Student\Models;

use App\Helpers\Json;

class IgnoreLearningModule extends Json
{
    public $id;
    public $reason;
    public $learning_module;
    public $student;
    public $storage_file;
    public $student_id;
    public $learning_module_id;
    public $storage_file_id;
}