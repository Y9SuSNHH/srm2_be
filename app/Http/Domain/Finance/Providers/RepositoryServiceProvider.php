<?php

namespace App\Http\Domain\Finance\Providers;

use App\Http\Domain\Finance\Repositories\Finance\FinanceRepository;
use App\Http\Domain\Finance\Repositories\Finance\FinanceRepositoryInterface;
use App\Http\Domain\Finance\Repositories\Transaction\TransactionRepository;
use App\Http\Domain\Finance\Repositories\Transaction\TransactionRepositoryInterface;
use App\Http\Domain\Finance\Repositories\FinancialCredit\FinancialCreditRepository;
use App\Http\Domain\Finance\Repositories\FinancialCredit\FinancialCreditRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(FinanceRepositoryInterface::class, FinanceRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(FinancialCreditRepositoryInterface::class, FinancialCreditRepository::class);
    }
}
