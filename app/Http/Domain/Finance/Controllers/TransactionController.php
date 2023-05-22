<?php

namespace App\Http\Domain\Finance\Controllers;

use App\Http\Domain\Finance\Repositories\Transaction\TransactionRepositoryInterface;
use App\Http\Domain\Finance\Requests\Transaction\CreateRequest;
use App\Http\Domain\Finance\Services\TransactionService;
use Illuminate\Http\JsonResponse;

/**
 * Class TransactionController
 * @package App\Http\Domain\Finance\Controllers
 */
class TransactionController
{
    private $transaction_repository;
    private $transaction_service;

    public function __construct(TransactionRepositoryInterface $repository, TransactionService $service)
    {
        $this->transaction_repository = $repository;
        $this->transaction_service = $service;
    }

    public function create(CreateRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->transaction_service->add($request));
    }

}
