<?php

namespace App\Providers;

use App\Eloquent\Permission;
use App\Eloquent\User as EloquentUser;
use App\Http\Enum\PermissionAction;
use App\Http\Enum\RoleAuthority;
use Illuminate\Auth\RequestGuard as IlluminateRequestGuard;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Builder;

class RequestGuard extends IlluminateRequestGuard implements Guard
{
    /** @var null|array */
    private $roles;
    /** @var null|array */
    private $permissions;
    /** @var null|int */
    private $role_authority;

    /**
     * check action eloquent: create
     *
     * @param string $model
     * @return bool
     * @throws \ReflectionException
     */
    public function creatable(string $model): bool
    {
        return $this->isPrivilege() || PermissionAction::CREATE()->validate($this->getAction($model));
    }

    /**
     * check action eloquent: edit
     * @param string $model
     * @return bool
     * @throws \ReflectionException
     */
    public function editable(string $model): bool
    {
        return $this->isPrivilege() || PermissionAction::EDIT()->validate($this->getAction($model));
    }

    /**
     * check action eloquent: delete
     *
     * @param string $model
     * @return bool
     * @throws \ReflectionException
     */
    public function deletable(string $model): bool
    {
        return $this->isPrivilege() || PermissionAction::DELETE()->validate($this->getAction($model));
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    public function isPrivilege(): bool
    {
        $this->permissionTags();

        return RoleAuthority::SUPPER_ADMIN()->validate($this->role_authority);
    }

    /**
     * @return array|null
     * @throws \ReflectionException
     */
    public function permissionTags(): ?array
    {
        if (!is_array($this->permissions)) {
            if ($user = $this->getEloquentUser()) {
                $user->load(['permissions:id,guard,action,constraint', 'roles' => function ($query) {
                    /** @var Builder $query */
                    $query->with('permissions:id,guard,action,constraint')
                        ->select(['id', 'name', 'authority']);
                }]);

                $this->permissions = [];

                foreach ($user->permissions as $permission) {
                    $this->retrieve($permission);
                }

                $roles = $user->roles;

                if ($roles->isNotEmpty()) {
                    $this->roles = [];
                    $this->role_authority = 0;

                    foreach ($roles as $role) {
                        $this->roles[] = $role->name;
                        $this->role_authority = $this->role_authority | $role->authority;

                        foreach ($role->permissions as $permission) {
                            $this->retrieve($permission);
                        }
                    }
                }
            }

            if (is_array($this->roles)) {
                $this->roles = array_unique($this->roles);
            }
        }

        return $this->permissions;
    }

    /**
     * @return array|null
     * @throws \ReflectionException
     */
    public function roles(): ?array
    {
        $this->permissionTags();
        return $this->roles;
    }

    /**
     * @return int|null
     * @throws \ReflectionException
     */
    public function roleAuthority(): ?int
    {
        $this->permissionTags();

        return $this->role_authority;
    }

    /**
     * @return EloquentUser|null
     */
    private function getEloquentUser(): ?EloquentUser
    {
        $user = $this->user()->getEloquentUser();

        if ($user instanceof EloquentUser) {
            return $user;
        }

        return null;
    }

    /**
     * @param Permission $permission
     * @throws \ReflectionException
     */
    private function retrieve(Permission $permission)
    {
        $guard = $permission->guard;

        if (isset($this->permissions[$guard])) {
            $old = PermissionAction::extract($this->permissions[$guard]['action']);
            $new = PermissionAction::extract($permission->action);
            $this->permissions[$guard]['action'] = PermissionAction::compact(array_unique(array_merge($old, $new)));
            $array = json_decode($permission->constraint, true);

            if (!empty($array)) {
                $this->permissions[$guard]['constraint'] = array_merge($this->permissions[$guard]['constraint'], array());
            }
        } else {
            $this->permissions[$guard] = [
                'action' => $permission->action,
                'constraint' => json_decode($permission->constraint, true) ?? [],
            ];
        }
    }

    /**
     * @param $key
     * @return int
     * @throws \ReflectionException
     */
    private function getAction($key): int
    {
        $this->permissionTags();
        return array_key_exists($key, $this->permissions) ? (int)$this->permissions[$key]['action'] : 0;
    }
}
