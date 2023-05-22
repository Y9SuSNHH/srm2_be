<?php

namespace App\Http\Domain\TrainingProgramme\Services;

use App\Http\Domain\TrainingProgramme\Repositories\EnrollmentObject\EnrollmentObjectRepositoryInterface;

class EnrollmentObjectService
{
    private $enrollment_object_repository;

    public function __construct(EnrollmentObjectRepositoryInterface $enrollment_object_repository)
    {
        $this->enrollment_object_repository = $enrollment_object_repository;
    }

    public function findExistedEnrollmentObjects(array $shortcodes)
    {
        return  $this->enrollment_object_repository->findEnrollmentObjectByShortcodes($shortcodes);
    }
}
