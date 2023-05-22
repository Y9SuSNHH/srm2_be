<?php

namespace App\Http\Domain\Contact\Repositories\Contact;

use App\Http\Domain\Contact\Requests\Contact\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface ContactRepositoryInterface
{
    /**
     * Get all
     * @return \Illuminate\Support\Collection
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator;

        /**
     * Create contact
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array;

    /**
     * Update contact
     * @param array $validator
     * @param int $id
     * @return array
     */
    public function update(int $id, array $validator): array;

    /**
     * Delete contact
     * @param int $id
     * @return array
     */
    public function delete(int $id): array;

    /**
     * Link contact
     * @param int $id
     * @return array
     */
    public function link(int $id): array;

    /**
     * @param array $attribute
     * @return bool
     */
    public function insert(array $attribute): bool;

    /**
     * @param array $staff_usernames
     * @return array
     */
    public function getStaffs(array $staff_usernames = []): array;
}
