<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class WorkflowValue
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $workflow_id
 * @property int $workflow_structure_id
 * @property string $target
 * @property string $value
 * @property $created_at
 * @property int $created_by
 * @property $updated_at
 * @property int $updated_by
 */
class WorkflowValue extends Model
{
    use SoftDeletedTime;

    protected $table = 'workflow_values';

    protected $fillable = [
        'created_by',
        'updated_by',
        'workflow_id',
        'workflow_structure_id',
        'target',
        'value',
    ];
}
