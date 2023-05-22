<?php

namespace App\Http\Domain\Reports\Repositories\StudentClassroom;

use App\Eloquent\StudentClassroom;

class StudentClassroomRepository implements StudentClassroomRepositoryInterface
{  
    private $model;

    public function __construct()
    {
        $this->model = StudentClassroom::query()->getModel();
    }

    public function insert(array $data)
    {
        return $this->model->newQuery()->insert($data);
    }
}
