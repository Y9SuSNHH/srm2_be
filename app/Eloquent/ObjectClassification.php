<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;

/**
 * Class ObjectClassification
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property string $name
 * @property string $abbreviation
 * @property string $description
 * @property int $created_by
 * @property \Carbon\Carbon created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 */
class ObjectClassification extends Model
{
    use HasSchool;

    protected $table = 'object_classifications';

    protected $fillable = [
        'school_id',
        'name',
        'abbreviation',
        'description',
        'created_by',
        'updated_by',
    ];
}
