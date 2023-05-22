<?php

namespace App\Http\Domain\Common\Repositories\CreditPrice;

use App\Eloquent\CreditPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CreditPriceRepository implements CreditPriceRepositoryInterface
{
    /** @var Builder|\Illuminate\Database\Eloquent\Model */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = CreditPrice::query()->getModel();
    }

    /**
     * @param $first_day_of_school
     * @return int
     */
    public function getCreditPrice($first_day_of_school): int
    {
        /** @var CreditPrice $query */
        $query = $this->model->where('effective_date', '<=', $first_day_of_school)->orderBy('effective_date', 'desc')->first();
        return (int)$query->price;
    }
}