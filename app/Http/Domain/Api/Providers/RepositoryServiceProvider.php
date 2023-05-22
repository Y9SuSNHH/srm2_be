<?php

namespace App\Http\Domain\Api\Providers;

use App\Http\Domain\Api\Repositories\Permission\PermissionRepository;
use App\Http\Domain\Api\Repositories\Permission\PermissionRepositoryInterface;
use App\Http\Domain\Api\Repositories\Role\RoleRepository;
use App\Http\Domain\Api\Repositories\Role\RoleRepositoryInterface;
use App\Http\Domain\Api\Repositories\School\SchoolRepository;
use App\Http\Domain\Api\Repositories\School\SchoolRepositoryInterface;
use App\Http\Domain\Api\Repositories\Staff\StaffRepository;
use App\Http\Domain\Api\Repositories\Staff\StaffRepositoryInterface;
use App\Http\Domain\Api\Repositories\StorageFile\StorageFileRepository;
use App\Http\Domain\Api\Repositories\StorageFile\StorageFileRepositoryInterface;
use App\Http\Domain\Api\Repositories\Student\GradeRepository;
use App\Http\Domain\Api\Repositories\Student\GradeRepositoryInterface;
use App\Http\Domain\Api\Repositories\User\UserRepository;
use App\Http\Domain\Api\Repositories\User\UserRepositoryInterface;
use App\Http\Domain\Api\Repositories\Register\RegisterRepository;
use App\Http\Domain\Api\Repositories\Register\RegisterRepositoryInterface;
use App\Http\Domain\Api\Repositories\Administrative\AdministrativeRepository;
use App\Http\Domain\Api\Repositories\Administrative\AdministrativeRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(SchoolRepositoryInterface::class, SchoolRepository::class);
        $this->app->bind(StaffRepositoryInterface::class, StaffRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(RegisterRepositoryInterface::class, RegisterRepository::class);
        $this->app->bind(AdministrativeRepositoryInterface::class, AdministrativeRepository::class);
        $this->app->bind(StorageFileRepositoryInterface::class, StorageFileRepository::class);
        $this->app->bind(GradeRepositoryInterface::class, GradeRepository::class);
    }
}
