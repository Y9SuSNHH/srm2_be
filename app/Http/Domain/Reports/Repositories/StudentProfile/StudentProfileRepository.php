<?php

namespace App\Http\Domain\Reports\Repositories\StudentProfile;

use App\Eloquent\StudentProfile;

class StudentProfileRepository implements StudentProfileRepositoryInterface
{  
    private $model;

    public function __construct()
    {
        $this->model = StudentProfile::query()->getModel();
    }

    public function findExistedStudents($profile_codes) {
        $student_profiles = $this->model->query()
                                        ->with('profile')
                                        ->whereIn('profile_code',$profile_codes)
                                        ->get();
        return $student_profiles;
    }

    public function insert(array $data)
    {
        return $this->model->newQuery()->insert($data);
    }
}
