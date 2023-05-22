<?php

namespace App\Http\Domain\Contact\Providers;

use App\Http\Domain\Contact\Repositories\Contact\ContactRepository;
use App\Http\Domain\Contact\Repositories\Contact\ContactRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        
    }
}
