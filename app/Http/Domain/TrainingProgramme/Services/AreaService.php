<?php

namespace App\Http\Domain\TrainingProgramme\Services;

use App\Http\Domain\TrainingProgramme\Repositories\Area\AreaRepositoryInterface;

class AreaService
{
    private $area_repository;

    public function __construct(AreaRepositoryInterface $area_repository)
    {
        $this->area_repository = $area_repository;
    }

    public function findExistedAreas(array $codes)
    {
        return  $this->area_repository->findAreaByCodes($codes);
    }
}
