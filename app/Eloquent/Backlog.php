<?php

namespace App\Eloquent;

/**
 * Class Backlog
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $user_id
 * @property int $school_id
 * @property int $work_div
 * @property int $work_status
 * @property string $work_payload
 * @property $reference
 * @property string $note
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Backlog extends Model
{
    protected $table = 'backlogs';

    protected $fillable = [
        'user_id',
        'school_id',
        'work_div',
        'work_status',
        'work_payload',
        'reference',
        'note',
    ];
}
