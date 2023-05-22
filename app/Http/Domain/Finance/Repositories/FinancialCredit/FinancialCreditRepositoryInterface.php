<?php

namespace App\Http\Domain\Finance\Repositories\FinancialCredit;

interface FinancialCreditRepositoryInterface
{
     /**
     * Create financial credit
     * @param array $validator
     * @return mixed
     */
    public function create(array $validator): mixed;
}
