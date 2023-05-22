<?php

namespace App\Http\Domain\Api\Models\Auth;

use App\Eloquent\User as EloquentUser;
use App\Helpers\Json;
use App\Helpers\Traits\CamelArrayAble;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class User
 * @package App\Http\Domain\Api\Models\Auth
 */
class User extends Json
{
    use CamelArrayAble;

    public $id;
    public $username;
    public $roles;
    public $permissions;
    public $staff;

    /** @var EloquentUser|null */
    private $eloquent_user;
    /** @var string|null */
    private $key_name;
    /** @var string|null */
    private $remember_token;

    /**
     * User constructor.
     * @param EloquentUser|null $eloquent_user
     * @param string|null $remember_token
     */
    public function __construct(?EloquentUser $eloquent_user, ?string $remember_token)
    {
        $this->eloquent_user = $eloquent_user;
        $this->remember_token = $remember_token;

        if ($eloquent_user && $remember_token) {
            $this->key_name = $eloquent_user->getKeyName();
            $this->id = $eloquent_user->id;
        }
    }

    /**
     * @return EloquentUser|null
     */
    public function getEloquentUser(): ?EloquentUser
    {
        return $this->eloquent_user;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->eloquent_user->username;
    }

    /**
     * @return string|null
     */
    public function getAuthIdentifierName(): ?string
    {
        return $this->key_name;
    }

    /**
     * @return string|null
     */
    public function getAuthIdentifier(): ?string
    {
        return $this->key_name;
    }

    /**
     * @return string|null
     */
    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function jsonSerialize()
    {
        if (!$this->eloquent_user) {
            return parent::jsonSerialize();
        }

        $this->loadRelations();
        return parent::jsonSerialize();
    }

    /**
     * @return int|null
     */
    public function getStaffId(): ?int
    {
        $this->loadRelations();

        if (!$this->staff) {
            return null;
        }

        return (int)$this->staff['id'] ?? null;
    }

    private function loadRelations()
    {
        if (!$this->username)  {
            $this->eloquent_user->load([
                'roles'  => function ($query) {
                    /** @var Builder $query */
                    $query->with('permissions:id,guard,action')
                        ->select(['id', 'name', 'description', 'authority']);
                },
                'permissions:id,guard,action',
                'staff' => function ($query) {
                    /** @var Builder $query */
                    $query->where('id', $this->eloquent_user->reference_id)->select(['id', 'fullname', 'email', 'team', 'day_off', 'status', 'user_id']);
                }
            ]);
            $this->username = $this->eloquent_user->username;
            $this->roles = $this->eloquent_user->roles->toArray();
            $this->permissions = $this->eloquent_user->permissions->toArray();
            $this->staff = optional($this->eloquent_user->staff)->toArray() ?? null;
        }
    }
}
