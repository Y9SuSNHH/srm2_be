<?php

namespace App\Http\Domain\Reports\Repositories\G820;

use App\Http\Domain\Reports\Requests\G820\SearchRequest;

interface G820RepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll($request);

}