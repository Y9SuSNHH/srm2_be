<?php

namespace App\Http\Domain\Api\Repositories\Administrative;

interface AdministrativeRepositoryInterface
{
    /**
     * Get all
     * @return array
     */
    public function getAll() :array;
}
