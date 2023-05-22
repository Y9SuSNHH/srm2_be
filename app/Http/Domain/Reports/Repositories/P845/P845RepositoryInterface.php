<?php

namespace App\Http\Domain\Reports\Repositories\P845;

use App\Http\Domain\Reports\Requests\P845\SearchRequest;

interface P845RepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll($request);

}