<?php

namespace App\Http\Domain\Api\Repositories\Staff;

use App\Http\Domain\Api\Requests\Staff\SearchRequest;

interface StaffRepositoryInterface
{
    /**
     * Get all
     * @return mixed
     */
    public function getAll();

    /**
     * Get all
     * @param SearchRequest $request
     * @return mixed
     */
    public function getOptions(SearchRequest $request);


}