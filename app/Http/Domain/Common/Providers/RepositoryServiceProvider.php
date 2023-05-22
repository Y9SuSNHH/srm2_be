<?php

namespace App\Http\Domain\Common\Providers;

use App\Http\Domain\Common\Repositories\Backlog\BacklogRepository;
use App\Http\Domain\Common\Repositories\Backlog\BacklogRepositoryInterface;
use App\Http\Domain\Common\Repositories\CreditPrice\CreditPriceRepository;
use App\Http\Domain\Common\Repositories\CreditPrice\CreditPriceRepositoryInterface;
use App\Http\Domain\Common\Repositories\StorageFile\StorageFileRepository;
use App\Http\Domain\Common\Repositories\StorageFile\StorageFileRepositoryInterface;
use App\Http\Domain\Common\Repositories\StudentClassroom\StudentClassroomRepository;
use App\Http\Domain\Common\Repositories\StudentClassroom\StudentClassroomRepositoryInterface;
use App\Http\Domain\Common\Repositories\StudentRevisionHistory\StudentRevisionHistoryRepository;
use App\Http\Domain\Common\Repositories\StudentRevisionHistory\StudentRevisionHistoryRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(CreditPriceRepositoryInterface::class, CreditPriceRepository::class);
        $this->app->bind(StudentRevisionHistoryRepositoryInterface::class, StudentRevisionHistoryRepository::class);
        $this->app->bind(StudentClassroomRepositoryInterface::class, StudentClassroomRepository::class);
        $this->app->singleton(StorageFileRepositoryInterface::class, StorageFileRepository::class);
        $this->app->singleton(BacklogRepositoryInterface::class, BacklogRepository::class);
    }
}
