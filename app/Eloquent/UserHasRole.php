<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserHasRole
 * @package App\Eloquent
 *
 * @property int $role_id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 */
class UserHasRole extends Model
{
    protected $table = 'user_has_roles';

    public $timestamps = null;

    protected $primaryKey = ['role_id', 'user_id'];

    public $incrementing = false;

    protected $fillable = [
        'role_id',
        'user_id',
        'created_by',
        'updated_by',
    ];
}
