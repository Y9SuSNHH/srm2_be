<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Staff
 * @package App\Eloquent
 *
 * @property int $id
 * @property string $fullname
 * @property string $email
 * @property string $team
 * @property \Carbon\Carbon $day_off
 * @property string $status
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 */
class Staff extends Model
{
    use SoftDeletedTime;

    protected $table = 'staffs';

    protected $fillable = [
        'fullname',
        'email',
        'team',
        'day_off',
        'status',
        'user_id',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['day_off'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
