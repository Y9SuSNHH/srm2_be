<?php

namespace App\Http\Domain\TrainingProgramme\Services;

use App\Http\Domain\TrainingProgramme\Repositories\StudySession\PeriodRepository;
use App\Http\Domain\TrainingProgramme\Repositories\StudySession\PeriodRepositoryInterface;

class StudySessionService
{
    /** @var PeriodRepository */
    private $period_repository;

    public function __construct(PeriodRepositoryInterface $repository)
    {
        $this->period_repository = $repository;
    }
}