<?php

namespace App\Http\Domain\Student\Repositories\Classroom;

interface ClassroomRepositoryInterface
{
    public function getByIds(array $ids = []);

    public function getByClassroom(string $classroom);
}