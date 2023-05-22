<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Providers;

use App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom\ClassRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom\ClassRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Grade\GradeRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Grade\GradeRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Handover\HandoverRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Handover\HandoverRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Student\StudentRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\StudyPlan\StudyPlanRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\StudyPlan\StudyPlanRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\StudentProfile\StudentProfileRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\StudentProfile\StudentProfileRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ClassRepositoryInterface::class, ClassRepository::class);
        $this->app->bind(StudyPlanRepositoryInterface::class, StudyPlanRepository::class);
        $this->app->bind(GradeRepositoryInterface::class, GradeRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(HandoverRepositoryInterface::class, HandoverRepository::class);
        $this->app->bind(StudentProfileRepositoryInterface::class, StudentProfileRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
    }
}
