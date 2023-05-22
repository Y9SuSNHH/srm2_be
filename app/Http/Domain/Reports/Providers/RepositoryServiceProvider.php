<?php

namespace App\Http\Domain\Reports\Providers;

use App\Http\Domain\Reports\Repositories\G120\G120Repository;
use App\Http\Domain\Reports\Repositories\G120\G120RepositoryInterface;
use App\Http\Domain\Reports\Repositories\G820\G820Repository;
use App\Http\Domain\Reports\Repositories\G820\G820RepositoryInterface;
use App\Http\Domain\Reports\Repositories\P825\P825Repository;
use App\Http\Domain\Reports\Repositories\P825\P825RepositoryInterface;
use App\Http\Domain\Reports\Repositories\P845\P845Repository;
use App\Http\Domain\Reports\Repositories\P845\P845RepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Http\Domain\Reports\Repositories\F111\F111Repository;
use App\Http\Domain\Reports\Repositories\F111\F111RepositoryInterface;
use App\Http\Domain\Reports\Repositories\Profile\ProfileRepository;
use App\Http\Domain\Reports\Repositories\Profile\ProfileRepositoryInterface;
use App\Http\Domain\Reports\Repositories\Student\StudentRepository;
use App\Http\Domain\Reports\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Reports\Repositories\StudentClassroom\StudentClassroomRepository;
use App\Http\Domain\Reports\Repositories\StudentClassroom\StudentClassroomRepositoryInterface;
use App\Http\Domain\Reports\Repositories\StudentProfile\StudentProfileRepository;
use App\Http\Domain\Reports\Repositories\StudentProfile\StudentProfileRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(G120RepositoryInterface::class, G120Repository::class);
        $this->app->bind(G820RepositoryInterface::class, G820Repository::class);
        $this->app->bind(P825RepositoryInterface::class, P825Repository::class);
        $this->app->bind(P845RepositoryInterface::class, P845Repository::class);
        $this->app->bind(F111RepositoryInterface::class, F111Repository::class);
        $this->app->bind(StudentProfileRepositoryInterface::class, StudentProfileRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(StudentClassroomRepositoryInterface::class, StudentClassroomRepository::class);
    }
}
