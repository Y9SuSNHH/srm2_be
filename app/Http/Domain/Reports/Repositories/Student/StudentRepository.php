<?php

namespace App\Http\Domain\Reports\Repositories\Student;

use App\Eloquent\Student;

class StudentRepository implements StudentRepositoryInterface
{  
    private $model;

    public function __construct()
    {
        $this->model = Student::query()->getModel();
    }

    public function getAccount() {
        $account = $this->model->query()
                               ->where('school_id',school()->getId())
                               ->pluck('account')
                               ->toArray();
        return $account;
    }

    public function insert(array $data)
    {
        return $this->model->newQuery()->insert($data);
    }
}
