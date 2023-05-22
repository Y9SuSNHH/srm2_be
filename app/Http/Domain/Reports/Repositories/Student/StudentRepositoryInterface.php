<?php


namespace App\Http\Domain\Reports\Repositories\Student;


interface StudentRepositoryInterface
{
    public function insert(array $data);
    public function getAccount();
}