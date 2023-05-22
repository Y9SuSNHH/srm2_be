<?php

namespace App\Http\Domain\Reports\Repositories\Profile;

use App\Eloquent\Profile;

class ProfileRepository implements ProfileRepositoryInterface
{  
    private $model;

    public function __construct()
    {
        $this->model = Profile::query()->getModel();
    }

    public function insert(array $data)
    {
        return $this->model->newQuery()->insert($data);
    }

    public function findExistedProfiles($identifications) {
        $profiles = $this->model->query()
                                        ->whereIn('identification',$identifications)
                                        ->get();
        return $profiles;
    }
}
