<?php

namespace App\Http\Domain\Student\Repositories\LearningProcess;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Domain\Student\Requests\LearningProcess\SearchRequest;

interface LearningProcessRepositoryInterface
{    
     /**
     * Get all
     * @return \Illuminate\Support\Collection
     */
    public function getAll(SearchRequest $request);

    /**
     * Create learning process
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array;
    
    /**
     * getListItems
     *
     * @return array
     */
    public function getListItems(): array;

    /**
     * getAmountsReceived
     *
     */
    public function getAmountsReceived(string $profile_code);
}