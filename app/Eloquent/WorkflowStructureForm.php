<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class WorkflowStructureForm
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $workflow_structure_id
 * @property string $apply_table
 * @property string $field_name
 * @property int $field_div
 * @property string $field_label
 * @property string $validate_regex
 * @property int $order_field
 * @property $created_at
 * @property int $created_by
 * @property $updated_at
 * @property int $updated_by
 */
class WorkflowStructureForm extends Model
{
    use SoftDeletedTime;

    protected $table = 'workflow_structure_forms';

    protected $fillable = [
        'created_by',
        'updated_by',
        'workflow_structure_id',
        'apply_table',
        'field_name',
        'field_div',
        'field_label',
        'validate_regex',
        'order_field',
    ];
}
