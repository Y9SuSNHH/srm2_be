<?php

namespace App\Http\Domain\Api\Repositories;

use App\Http\Domain\Api\Repositories\Area\AreaRepository;
use App\Http\Domain\Api\Repositories\Area\AreaRepositoryInterface;
use App\Http\Domain\Api\Repositories\EnrollmentWave\EnrollmentWaveRepository;
use App\Http\Domain\Api\Repositories\EnrollmentWave\EnrollmentWaveRepositoryInterface;
use App\Http\Domain\Api\Repositories\Major\MajorRepository;
use App\Http\Domain\Api\Repositories\Major\MajorRepositoryInterface;
use App\Http\Domain\Api\Repositories\Object\ObjectRepository;
use App\Http\Domain\Api\Repositories\Object\ObjectRepositoryInterface;
use App\Http\Domain\Api\Repositories\ObjectClassification\ObjectTypeRepository;
use App\Http\Domain\Api\Repositories\ObjectClassification\ObjectTypeRepositoryInterface;
use App\Http\Domain\Api\Repositories\School\SchoolRepository;
use App\Http\Domain\Api\Repositories\School\SchoolRepositoryInterface;
use App\Http\Domain\Api\Repositories\Staff\StaffRepository;
use App\Http\Domain\Api\Repositories\Staff\StaffRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(SchoolRepositoryInterface::class, SchoolRepository::class);
        $this->app->bind(EnrollmentWaveRepositoryInterface::class, EnrollmentWaveRepository::class);
        $this->app->bind(MajorRepositoryInterface::class, MajorRepository::class);
        $this->app->bind(ObjectRepositoryInterface::class, ObjectRepository::class);
        $this->app->bind(ObjectTypeRepositoryInterface::class, ObjectTypeRepository::class);
        $this->app->bind(AreaRepositoryInterface::class, AreaRepository::class);
        $this->app->bind(StaffRepositoryInterface::class, StaffRepository::class);
    }
}
