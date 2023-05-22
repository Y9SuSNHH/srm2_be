<?php

namespace App\Http\Domain\Finance\Repositories\Transaction;

interface TransactionRepositoryInterface
{            
    /**
     * create
     *
     * @param  mixed $attributes
     * @return mixed
     */
    public function create(array $attributes): mixed;
    public function getTransactionByCode(string $code);
}
