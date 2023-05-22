<?php

namespace App\Http\Domain\Receivable\Providers;

// use App\Http\Domain\StudentReceivable\Repositories\Classroom\ClassroomRepository;
// use App\Http\Domain\StudentReceivable\Repositories\Classroom\ClassroomRepositoryInterface;
use App\Http\Domain\Receivable\Repositories\Receivable\ReceivableRepository;
use App\Http\Domain\Receivable\Repositories\Receivable\ReceivableRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ReceivableRepositoryInterface::class, ReceivableRepository::class);
        // $this->app->bind(ClassroomRepositoryInterface::class, ClassroomRepository::class);
    }
}
