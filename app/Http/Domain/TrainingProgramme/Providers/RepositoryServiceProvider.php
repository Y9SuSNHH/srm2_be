<?php

namespace App\Http\Domain\TrainingProgramme\Providers;

use App\Http\Domain\TrainingProgramme\Repositories\Area\AreaRepository;
use App\Http\Domain\TrainingProgramme\Repositories\Area\AreaRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\Classroom\ClassRepository;
use App\Http\Domain\TrainingProgramme\Repositories\Classroom\ClassRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\EnrollmentWave\EnrollmentWaveRepository;
use App\Http\Domain\TrainingProgramme\Repositories\EnrollmentWave\EnrollmentWaveRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\Major\MajorRepository;
use App\Http\Domain\TrainingProgramme\Repositories\Major\MajorRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\EnrollmentObject\EnrollmentObjectRepository;
use App\Http\Domain\TrainingProgramme\Repositories\EnrollmentObject\EnrollmentObjectRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\Curriculum\CurriculumRepository;
use App\Http\Domain\TrainingProgramme\Repositories\Curriculum\CurriculumRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\StudySession\PeriodRepository;
use App\Http\Domain\TrainingProgramme\Repositories\StudySession\PeriodRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\StudyPlan\StudyPlanRepository;
use App\Http\Domain\TrainingProgramme\Repositories\StudyPlan\StudyPlanRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\Period\PeriodsRepository;
use App\Http\Domain\TrainingProgramme\Repositories\Period\PeriodsRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\LearningModule\LearningModuleRepository;
use App\Http\Domain\TrainingProgramme\Repositories\LearningModule\LearningModuleRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\Subject\SubjectRepository;
use App\Http\Domain\TrainingProgramme\Repositories\Subject\SubjectRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\MajorObjectMap\MajorObjectMapRepository;
use App\Http\Domain\TrainingProgramme\Repositories\MajorObjectMap\MajorObjectMapRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
//        $this->app->bind(SchoolRepositoryInterface::class, SchoolRepository::class);
        $this->app->bind(EnrollmentWaveRepositoryInterface::class, EnrollmentWaveRepository::class);
        $this->app->bind(MajorRepositoryInterface::class, MajorRepository::class);
        $this->app->bind(EnrollmentObjectRepositoryInterface::class, EnrollmentObjectRepository::class);
//        $this->app->bind(LearningModuleRepositoryInterface::class, LearningModuleRepository::class);
        $this->app->bind(AreaRepositoryInterface::class, AreaRepository::class);
        $this->app->bind(CurriculumRepositoryInterface::class, CurriculumRepository::class);
        $this->app->bind(PeriodRepositoryInterface::class, PeriodRepository::class);
        $this->app->singleton(ClassRepositoryInterface::class, ClassRepository::class);
        $this->app->bind(StudyPlanRepositoryInterface::class, StudyPlanRepository::class);
        $this->app->bind(PeriodsRepositoryInterface::class, PeriodsRepository::class);
        $this->app->bind(LearningModuleRepositoryInterface::class, LearningModuleRepository::class);
        $this->app->bind(SubjectRepositoryInterface::class, SubjectRepository::class);
        $this->app->bind(MajorObjectMapRepositoryInterface::class, MajorObjectMapRepository::class);
    }
}
