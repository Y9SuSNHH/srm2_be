<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Period
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property int $classroom_id
 * @property int $semester
 * @property \Carbon\Carbon $decision_date
 * @property \Carbon\Carbon $collect_began_date
 * @property \Carbon\Carbon $collect_ended_date
 * @property \Carbon\Carbon $learn_began_date
 * @property \Carbon\Carbon $expired_date_com
 * @property \Carbon\Carbon $learn_ended_date
 * @property int $is_final
 * @property int $wait
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 */
class Period extends Model
{
    use HasSchool;

    protected $table = 'periods';

    protected $fillable = [
        'school_id',
        'classroom_id',
        'semester',
        'decision_date',
        'collect_began_date',
        'collect_ended_date',
        'learn_began_date',
        'expired_date_com',
        'learn_ended_date',
        'is_final',
        'wait',
        'created_by',
        'updated_by',
    ];

    protected $dates = [
        'decision_date',
        'collect_began_date',
        'collect_ended_date',
        'learn_began_date',
        'expired_date_com',
        'learn_ended_date',
    ];

     /**
     * @return BelongsTo
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
