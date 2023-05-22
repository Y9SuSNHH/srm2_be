<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Permission
 * @package App\Eloquent
 *
 * @property int $id
 * @property string $guard
 * @property int $action
 * @property string $constraint
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property Permission[]|\Illuminate\Database\Eloquent\Collection $roles
 */
class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'guard',
        'action',
        'constraint',
        'created_by',
        'updated_by',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_has_permissions');
    }
}
