<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserHasPermission
 * @package App\Eloquent
 *
 * @property int $permission_id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 */
class UserHasPermission extends Model
{
    protected $table = 'user_has_permissions';

    public $timestamps = null;

    protected $fillable = [
        'permission_id',
        'user_id',
        'created_by',
        'updated_by',
    ];
}
