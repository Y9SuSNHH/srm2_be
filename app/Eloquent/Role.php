<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 * @package App\Eloquent
 *
 * @property int $id
 * @property string $name
 * @property int $authority
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property Permission[]|\Illuminate\Database\Eloquent\Collection $permissions
 * @property User[]|\Illuminate\Database\Eloquent\Collection $users
 */
class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'authority',
        'description',
        'created_by',
        'updated_by',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_has_roles');
    }
}
