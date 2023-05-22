<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\StudyPlan;

use App\Eloquent\StudyPlan;
use App\Eloquent\Classroom;
use App\Eloquent\CreditPrice;
use App\Http\Domain\TrainingProgramme\Models\StudyPlan\StudyPlan as StudyPlanModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Domain\TrainingProgramme\Requests\StudyPlan\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudyPlanRepository implements StudyPlanRepositoryInterface
{
    /** @var Builder|\Illuminate\Database\Eloquent\Model */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = StudyPlan::query()->getModel();
    }

    /**
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator
    {
        $query = $this->studyPlanRepositoryQuery($request);
        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        return $paginate;
    }
    public function getListPrice(): array
    {
        $price = CreditPrice::query()
            ->orderBy('effective_date', 'desc')->get()
            ->transform(function ($val) {
                return [
                    'id'      => $val->id,
                    'effective_date' => $val->effective_date,
                    'price'    => $val->price,
                ];
            })->toArray();
        return $price;
    }

    /**
     * @param SearchRequest $request
     * @return array
     */
    public function options(SearchRequest $request): array
    {
        return $this->studyPlanRepositoryQuery($request)->get()->toArray();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        $study_plan = StudyPlan::query()->findOrFail($id);
        return (array)new StudyPlanModel($study_plan);
    }

    /**
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array
    {
        try {
            $validator['school_id'] = school()->getId();
            $study_plan = $this->model->create($validator);
            return (array)new StudyPlanModel($study_plan);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param int $id
     * @param array $validator
     * @return array
     */
    public function update(int $id, array $validator): array
    {
        try {
            $study_plan = StudyPlan::query()->findOrFail($id);
            $study_plan->update($validator);
            return (array)new StudyPlanModel($study_plan);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        try {
            $now = Carbon::now();
            $study_plan = StudyPlan::query()
                ->where('id', $id)
                ->where('study_began_date', '<', $now)
                ->first();
            $learning = StudyPlan::query()
                ->where('id', $id)
                ->whereExists(function ($q) {
                    $q->select('id')->from('training_program_items')->whereRaw('learning_module_id = study_plans.learning_module_id');
                })
                ->whereExists(function ($q) {
                    $q->select('id')->from('grades')->whereRaw('learning_module_id = study_plans.learning_module_id');
                })
                ->whereExists(function ($q) {
                    $q->select('id')->from('grade_settings')->whereRaw('learning_module_id = study_plans.learning_module_id');
                })
                ->first();
            if ($study_plan || $learning) {
                throw new \Exception("Xóa KHHT #{$id} thất bại, do KHHT đã được sử dụng");
            }
            StudyPlan::query()->findOrFail($id)->delete();
            return (array)'delete successful';
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function export(SearchRequest $request): mixed
    {
        $result = $this->studyPlanRepositoryQuery($request)->get();
        return $result;
    }

    /**
     * @param SearchRequest $request
     * @return Builder|\Illuminate\Database\Eloquent\Model
     */
    private function studyPlanRepositoryQuery(SearchRequest $request): Builder|\Illuminate\Database\Eloquent\Model
    {
        $query = Classroom::with([
            'major', 'area',
            'enrollmentWave',
            'studyPlans' => function ($q)  use ($request) {
                $q->with([
                    'learningModule' => function ($q) {
                        $q->with('subject');
                    },
                ]);
                $q->orderBy('semester', 'asc')->orderBy('slot', 'asc');
                if ($request->slot && $request->slot != '' && $request->slot != 'all') {
                    $q->where('slot', $request->slot);
                }
                if ($request->semester && $request->semester != '' && $request->semester != 'all') {
                    $q->where('semester', $request->semester);
                }
            }
        ]);
        $query->whereExists(function ($query) use ($request) {
            /** @var Builder $query */
            $query->select('id')
                ->from('study_plans')
                ->whereRaw('study_plans.classroom_id=classrooms.id');
        });
        $query->orderBy('id', 'desc');

        if ($request->first_day_of_school && $request->first_day_of_school != '' && $request->first_day_of_school != 'all') {
            $query->whereRelation('enrollmentWave', DB::raw('DATE(first_day_of_school)'), '=', $request->first_day_of_school);
        }
        if ($request->slot && $request->slot != '' && $request->slot != 'all') {
            $query->whereRelation('studyPlans', 'slot', '=', $request->slot);
        }
        if ($request->semester && $request->semester != '' && $request->semester != 'all') {
            $query->whereRelation('studyPlans', 'semester', '=', $request->semester);
        }
        if ($request->classroom_id && $request->classroom_id != '' && $request->classroom_id != 'all') {
            $query->whereIn('id', explode(',', $request->classroom_id));
        }
        if ($request->area_id && $request->area_id != '' && $request->area_id != 'all') {
            $query->where('area_id', $request->area_id);
        }
        if ($request->major_id && $request->major_id != '' && $request->major_id != 'all') {
            $query->where('major_id', $request->major_id);
        }
        return $query;
    }

    /**
     * getCodeAndAccount
     *
     * @return array
     */
    public function getCodeAndAccount(): array
    {
        $now = Carbon::now();
        $query = $this->model->query()
            ->whereDate('study_began_date', '<=', $now)
            ->whereDate('study_ended_date', '>=', $now)
            ->leftJoin('learning_modules', 'study_plans.learning_module_id', '=', 'learning_modules.id')
            ->leftJoin('classrooms', 'study_plans.classroom_id', '=', 'classrooms.id')
            ->leftJoin('student_classrooms', 'classrooms.id', '=', 'student_classrooms.classroom_id')
            ->leftJoin('students', 'student_classrooms.student_id', '=', 'students.id')
            ->select('learning_modules.code as learning_module_code', 'students.account')
            ->get()->toArray();
        return $query;
    }
}
