<?php

namespace App\Http\Domain\Finance\Repositories\FinancialCredit;

use App\Eloquent\FinancialCredit;
use App\Http\Domain\Finance\Repositories\FinancialCredit\FinancialCreditRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Domain\Finance\Models\FinancialCredit\FinancialCredit as FinancialCreditModel;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\Traits\ThrowIfNotAble;

/**
 * Class FinancialCreditRepository
 * @package App\Http\Domain\Finance\Repositories\FinancialCredit
 */
class FinancialCreditRepository implements FinancialCreditRepositoryInterface
{
    use ThrowIfNotAble;

    /** @var Builder|\Illuminate\Database\Eloquent\Model */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = FinancialCredit::query()->getModel();
    }

    /**
     * @param array $validator
     * @return mixed
     */
    public function create(array $validator): mixed
    {
        try {
            return $this->model->insertGetId($validator);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }
    
}
