<?php

namespace App\Http\Domain\Student\Repositories\Profile;

interface ProfileRepositoryInterface
{
    public function getById(int $id);
    public function update(int $id, array $data);
}