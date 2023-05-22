<?php

namespace App\Http\Domain\Reports\Repositories\P825;

use App\Http\Domain\Reports\Requests\P825\SearchRequest;

interface P825RepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll($request);

}