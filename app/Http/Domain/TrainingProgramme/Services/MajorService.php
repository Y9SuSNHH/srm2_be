<?php

namespace App\Http\Domain\TrainingProgramme\Services;

use App\Http\Domain\TrainingProgramme\Repositories\Major\MajorRepositoryInterface;

class MajorService
{
    private $major_repository;

    public function __construct(MajorRepositoryInterface $major_repository)
    {
        $this->major_repository = $major_repository;
    }

    public function findExistedMajors(array $shortcodes)
    {
        return  $this->major_repository->findMajorByShortcodes($shortcodes);
    }
}
