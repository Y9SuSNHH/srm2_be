<?php

namespace App\Http\Domain\Student\Repositories\StudentProfile;

use ReflectionException;
use Throwable;

interface StudentProfileRepositoryInterface
{
    /**
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    public function update(int $id, array $data): mixed;

    public function getByProfileCode(string $profile_code);
}