<?php

namespace App\Http\Domain\Student\Providers;

use App\Http\Domain\Student\Repositories\Classroom\ClassroomRepository;
use App\Http\Domain\Student\Repositories\Classroom\ClassroomRepositoryInterface;
use App\Http\Domain\Student\Repositories\IgnoreLearningModule\IgnoreLearningModuleRepository;
use App\Http\Domain\Student\Repositories\IgnoreLearningModule\IgnoreLearningModuleRepositoryInterface;
use App\Http\Domain\Student\Repositories\LearningModule\LearningModuleRepository;
use App\Http\Domain\Student\Repositories\LearningModule\LearningModuleRepositoryInterface;
use App\Http\Domain\Student\Repositories\Petition\PetitionRepository;
use App\Http\Domain\Student\Repositories\Petition\PetitionRepositoryInterface;
use App\Http\Domain\Student\Repositories\Profile\ProfileRepository;
use App\Http\Domain\Student\Repositories\Profile\ProfileRepositoryInterface;
use App\Http\Domain\Student\Repositories\PetitionFlow\PetitionFlowRepository;
use App\Http\Domain\Student\Repositories\PetitionFlow\PetitionFlowRepositoryInterface;
use App\Http\Domain\Student\Repositories\Student\StudentRepository;
use App\Http\Domain\Student\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Student\Repositories\StudentClassroom\StudentClassroomRepository;
use App\Http\Domain\Student\Repositories\StudentClassroom\StudentClassroomRepositoryInterface;
use App\Http\Domain\Student\Repositories\StudentProfile\StudentProfileRepositoryInterface;
use App\Http\Domain\Student\Repositories\StudentProfile\StudentProfileRepository;
use App\Http\Domain\Student\Repositories\StudentRevisionHistory\StudentRevisionHistoryRepository;
use App\Http\Domain\Student\Repositories\StudentRevisionHistory\StudentRevisionHistoryRepositoryInterface;
use App\Http\Domain\Student\Repositories\LearningProcess\LearningProcessRepository;
use App\Http\Domain\Student\Repositories\LearningProcess\LearningProcessRepositoryInterface;
use App\Http\Domain\Student\Repositories\CareHistory\CareHistoryRepository;
use App\Http\Domain\Student\Repositories\CareHistory\CareHistoryRepositoryInterface;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(StudentRevisionHistoryRepositoryInterface::class, StudentRevisionHistoryRepository::class);
        $this->app->bind(StudentClassroomRepositoryInterface::class, StudentClassroomRepository::class);
        $this->app->bind(PetitionRepositoryInterface::class, PetitionRepository::class);
        $this->app->bind(PetitionFlowRepositoryInterface::class, PetitionFlowRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(StudentProfileRepositoryInterface::class, StudentProfileRepository::class);
        $this->app->bind(ClassroomRepositoryInterface::class, ClassroomRepository::class);
        $this->app->bind(LearningProcessRepositoryInterface::class, LearningProcessRepository::class);
        $this->app->bind(CareHistoryRepositoryInterface::class, CareHistoryRepository::class);
        $this->app->bind(LearningModuleRepositoryInterface::class, LearningModuleRepository::class);
        $this->app->bind(IgnoreLearningModuleRepositoryInterface::class, IgnoreLearningModuleRepository::class);
    }
}
