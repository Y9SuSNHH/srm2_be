<?php

namespace App\Helpers\Interfaces;

interface PaginateSearchRequest
{
    /**
     * @return int|null
     */
    public function page(): ?int;

    /**
     * @return int|null
     */
    public function perPage(): ?int;
}
