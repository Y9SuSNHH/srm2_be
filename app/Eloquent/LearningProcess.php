<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Class LearningProcess
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $learning_modules_id
 * @property int $student_id
 * @property int $result_btgk1
 * @property int $result_btgk2
 * @property int $result_diem_cc
 * @property \Carbon\Carbon $deadline_btgk1
 * @property \Carbon\Carbon $deadline_btgk2
 * @property \Carbon\Carbon $deadline_diem_cc
 * @property string $item_type
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 */
class LearningProcess extends Model
{
    protected $table = 'learning_processes';

    protected $fillable = [
        'learning_modules_id',
        'student_id',
        'result_btgk1',
        'result_btgk2',
        'result_diem_cc',
        'deadline_btgk1',
        'deadline_btgk2',
        'deadline_diem_cc',
        'item_type',
        'created_by',
        'updated_by',
    ];

    /**
     * @return BelongsTo
     */
    public function learningModule(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class, 'learning_modules_id');
    }
}
