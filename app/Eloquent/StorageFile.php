<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;

/**
 * Class StorageFile
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property int $storage_div
 * @property int $file_div
 * @property int $file_path
 * @property int $file_type
 * @property int $file_size
 * @property int $origin_name
 * @property int $uploader
 * @property int $file_url
 * @property int $created_by
 * @property int $updated_by
 */
class StorageFile extends Model
{
    use HasSchool;

    protected $table = 'storage_files';

    protected $fillable = [
        'school_id',
        'storage_div',
        'file_path',
        'file_type',
        'file_size',
        'origin_name',
        'file_div',
        'uploader',
        'file_url',
        'created_by',
        'updated_by',
    ];
}
