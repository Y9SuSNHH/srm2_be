<?php

namespace App\Http\Domain\Common\Repositories\CreditPrice;

use Illuminate\Pagination\LengthAwarePaginator;

interface CreditPriceRepositoryInterface
{
    /**
     * @param $first_day_of_school
     * @return int
     */
    public function getCreditPrice($first_day_of_school):int;

}