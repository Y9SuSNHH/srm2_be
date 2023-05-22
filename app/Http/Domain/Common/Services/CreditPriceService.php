<?php

namespace App\Http\Domain\Common\Services;

use App\Http\Domain\Common\Repositories\CreditPrice\CreditPriceRepositoryInterface;

class CreditPriceService {
  private $responsitory;

  public function __construct(){
    $this->responsitory = app(CreditPriceRepositoryInterface::class);
  }

}