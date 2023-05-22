<?php

namespace App\Eloquent;

use App\Http\Enum\ReferenceType;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

/**
 * Class User
 * @package App\Eloquent
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property int $reference_type
 * @property int $reference_id
 *
 * @property Role[]|\Illuminate\Database\Eloquent\Collection $roles
 * @property Permission[]|\Illuminate\Database\Eloquent\Collection $permissions
 * @property Staff $staff
 */
class User extends Model implements Authenticatable, AuthorizableContract
{
    use Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username',
        'password',
        'reference_type',
        'reference_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @var string
     */
    private $token;

    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPassword()
    {
        return null;
    }

    public function getRememberToken()
    {
        return $this->token;
    }

    public function setRememberToken($value)
    {
        if (!$this->token) {
            $this->token = $value;
        }
    }

    public function getRememberTokenName()
    {
        return '';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_has_roles');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_has_permissions');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function staff(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * @return void
     */
    public function loadRelation(): void
    {
        $relations = [
            'roles'  => function ($query) {
                /** @var Builder $query */
                $query->with('permissions:id,guard,action')
                    ->select(['id', 'name', 'description', 'authority']);
            },
            'permissions:id,guard,action'
        ];

        $reference_type = $this->reference_type;
        $reference_id = $this->reference_id;

        switch ($reference_type) {
            case ReferenceType::STAFF:
                $relations['staff'] = function ($query) use ($reference_id) {
                    /** @var Builder $query */
                    $query->where('id', $reference_id)->select(['fullname', 'email', 'team', 'day_off', 'status', 'user_id']);
                };
                break;
            default:
                ;
        }

        $this->load($relations);
    }

}
