<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Classroom;

interface ClassRepositoryInterface
{
    /**
     * get all classroom
     * return key:classroom code => value:id
     * @return array
     */
    public function getClassroomCode(): array;
}
