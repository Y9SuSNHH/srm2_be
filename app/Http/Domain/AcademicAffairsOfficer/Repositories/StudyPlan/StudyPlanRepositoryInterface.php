<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\StudyPlan;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface StudyPlanRepositoryInterface
{
    /**
     * @param bool $done
     * @return Collection
     */
    public function getExam(bool $done = false): Collection;

    /**
     * @param int $id
     * @return mixed
     */
    public function findLearningModule(int $id): mixed;

    /**
     * @param int $learning_module_id
     * @param Carbon|null $day_of_the_test
     * @return mixed
     */
    public function findExamPlan(int $learning_module_id, Carbon $day_of_the_test = null): mixed;
}