<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;

/**
 * Class ApprovalGroupUser
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property $name
 * @property $created_at
 * @property int $created_by
 * @property $updated_at
 * @property int $updated_by
 *
 * @property User[]|\Illuminate\Database\Eloquent\Collection $users
 */
class ApprovalGroupUser extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'approval_group_users';

    protected $fillable = [
        'created_by',
        'updated_by',
        'school_id',
        'name',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'map_approval_group_users')->withPivot('is_decision');
    }
}