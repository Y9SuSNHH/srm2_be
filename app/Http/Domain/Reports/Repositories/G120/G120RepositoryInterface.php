<?php

namespace App\Http\Domain\Reports\Repositories\G120;

use App\Http\Domain\Reports\Requests\G120\SearchRequest;

interface G120RepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll($request);

}