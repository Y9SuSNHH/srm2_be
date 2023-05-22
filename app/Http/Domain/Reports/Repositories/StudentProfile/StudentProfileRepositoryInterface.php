<?php


namespace App\Http\Domain\Reports\Repositories\StudentProfile;


interface StudentProfileRepositoryInterface
{
    public function insert(array $data);
    public function findExistedStudents($profile_codes);
}