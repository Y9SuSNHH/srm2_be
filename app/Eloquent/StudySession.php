<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class StudySession
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $semester
 * @property int $classroom_id
 * @property int $began_study_plan_id
 * @property int $ended_study_plan_id
 * @property \Carbon\Carbon $decision_date
 * @property \Carbon\Carbon $collect_began_date
 * @property \Carbon\Carbon $collect_ended_date
 * @property \Carbon\Carbon $study_began_date
 * @property \Carbon\Carbon $expired_date_com
 * @property \Carbon\Carbon $study_ended_date
 * @property int $is_final
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 */
class StudySession extends Model
{
    use SoftDeletedTime;

    protected $table = 'study_sessions';

    protected $fillable = [
        'semester',
        'classroom_id',
        'began_study_plan_id',
        'ended_study_plan_id',
        'decision_date',
        'collect_began_date',
        'collect_ended_date',
        'study_began_date',
        'expired_date_com',
        'study_ended_date',
        'is_final',
        'created_by',
        'updated_by',
    ];

    protected $dates = [
        'decision_date',
        'collect_began_date',
        'collect_ended_date',
        'study_began_date',
        'expired_date_com',
        'study_ended_date',
    ];
}