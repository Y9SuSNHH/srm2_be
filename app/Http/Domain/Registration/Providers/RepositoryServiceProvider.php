<?php

namespace App\Http\Domain\Registration\Providers;

use App\Http\Domain\Registration\Repositories\Registration\RegistrationRepository;
use App\Http\Domain\Registration\Repositories\Registration\RegistrationRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(RegistrationRepositoryInterface::class, RegistrationRepository::class);  
    }
}
