<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\StudyPlan;

use App\Eloquent\LearningModule;
use App\Eloquent\StudyPlan as EloquentStudyPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StudyPlanRepository implements StudyPlanRepositoryInterface
{
    /**
     * @param bool $done
     * @return Collection
     */
    public function getExam(bool $done = false): Collection
    {
        $query = EloquentStudyPlan::query()
            ->with(['learningModule' => function($query) {
                /** @var Builder $query */
                $query->join('subjects', 'subjects.id', '=', 'learning_modules.subject_id')
                    ->select(['learning_modules.id', 'subjects.name', 'learning_modules.code', 'learning_modules.amount_credit', 'learning_modules.grade_setting_div']);
            }, 'classroom:id,code'])
//            ->whereExists(function ($query) {
//                /** @var Builder $query */
//                $query->select('id')->from('grade_settings')
//                    ->where(function ($query) {
//                        /** @var Builder $query */
//                        $query->where('deleted_time', 0)
//                            ->orWhereNull('deleted_time');
//                    })
//                    ->whereRaw('grade_settings.learning_module_id=study_plans.learning_module_id');
//            })
            ->whereHas('classroom');

        if ($done) {
            $query = $query->whereNotNull('day_of_the_test')
                ->whereDate('day_of_the_test', '<', Carbon::now());
        }

        return $query->get(['id', 'learning_module_id', 'day_of_the_test', 'classroom_id']);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findLearningModule(int $id): mixed
    {
        return LearningModule::query()->with('subject:id,name')->findOrFail($id);
    }

    /**
     * @param int $learning_module_id
     * @param Carbon|null $day_of_the_test
     * @return mixed
     */
    public function findExamPlan(int $learning_module_id, Carbon $day_of_the_test = null): mixed
    {
        $query = EloquentStudyPlan::query()
            ->with([
                'classroom:id,code'
            ])
            ->where('learning_module_id', $learning_module_id);

        if ($day_of_the_test) {
            $query->whereDate('day_of_the_test', $day_of_the_test);
        }

        return $query->get([
            'id',
            'classroom_id',
            'semester',
            'slot',
            'learning_module_id',
            'subject_id',
            'study_began_date',
            'study_ended_date',
            'day_of_the_test',
            ]);
    }
}
