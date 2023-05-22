<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\LearningModule;

use App\Eloquent\LearningModule;
use App\Eloquent\Grade;
use App\Eloquent\GradeSetting;
use App\Eloquent\CurriculumItems;
use App\Helpers\Interfaces\ThrowIfNotAbleInterface;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\TrainingProgramme\Models\LearningModule\LearningModule as LearningModuleModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Domain\TrainingProgramme\Requests\LearningModule\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
class LearningModuleRepository implements LearningModuleRepositoryInterface, ThrowIfNotAbleInterface
{
    use ThrowIfNotAble;

    /** @var Builder|\Illuminate\Database\Eloquent\Model */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = LearningModule::query()->getModel();
    }

    /**
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator
    {
        $query = $this->LearningModuleRepositoryQuery($request);
        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        $paginate->getCollection()->transform(function ($learning_module) {
            return new LearningModuleModel($learning_module);
        });
        return $paginate;
    }

    /**
     * @param SearchRequest|null $request
     * @return array
     */
    public function options(SearchRequest $request = null): array
    {
        return $this->LearningModuleRepositoryQuery($request)
            ->get()
            ->transform(function ($learning_module) {
                return new LearningModuleModel($learning_module);
            })
            ->toArray();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        $learning_module = LearningModule::query()->findOrFail($id);
        return (array)new LearningModuleModel($learning_module);
    }

    /**
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array
    {
        return $this->createAble(LearningModule::class, function () use ($validator) {
            $learning_module = $this->model->create($validator);
            return (array)new LearningModuleModel($learning_module);
        });
    }

    /**
     * @param int $id
     * @param array $validator
     * @return array
     */
    public function update(int $id, array $validator): array
    {
        try {
            $learning = LearningModule::query()
                ->where('id', $id)
                ->whereNotExists(function ($q) {
                    $q->select('id')->from('training_program_items')->whereRaw('learning_module_id = learning_modules.id')->whereRaw('(deleted_time IS NULL OR deleted_time=0)');
                })
                ->whereNotExists(function ($q) {
                    $q->select('id')->from('study_plans')->whereRaw('learning_module_id = learning_modules.id')->whereRaw('(deleted_time IS NULL OR deleted_time=0)');
                })
                ->whereNotExists(function ($q) {
                    $q->select('id')->from('grades')->whereRaw('learning_module_id = learning_modules.id');
                })
                ->whereNotExists(function ($q) {
                    $q->select('id')->from('grade_settings')->whereRaw('learning_module_id = learning_modules.id')->whereRaw('(deleted_time IS NULL OR deleted_time=0)');
                })
                ->first();

            if (!$learning) {
                throw new \Exception("Sửa học phần #{$id} thất bại, do học phần đang được sử dụng");
            }
            $learning_module = LearningModule::query()->findOrFail($id);
            $learning_module->update($validator);
            return (array)new LearningModuleModel($learning_module);
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
            $learning = LearningModule::query()
                ->where('id', $id)
                ->whereNotExists(function ($q) {
                    $q->select('id')->from('training_program_items')->whereRaw('learning_module_id = learning_modules.id');
                })
                ->whereNotExists(function ($q) {
                    $q->select('id')->from('study_plans')->whereRaw('learning_module_id = learning_modules.id');
                })
                ->whereNotExists(function ($q) {
                    $q->select('id')->from('grades')->whereRaw('learning_module_id = learning_modules.id');
                })
                ->whereNotExists(function ($q) {
                    $q->select('id')->from('grade_settings')->whereRaw('learning_module_id = learning_modules.id');
                })
                ->first();

            if (!$learning) {
                throw new \Exception("Xóa học phần #{$id} thất bại, do học phần đang được sử dụng");
            }
            LearningModule::query()->findOrFail($id)->delete();
            return (array)'delete successful';
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param SearchRequest|null $request
     * @return Builder|\Illuminate\Database\Eloquent\Model
     */
    private function LearningModuleRepositoryQuery(SearchRequest $request = null): Builder|\Illuminate\Database\Eloquent\Model
    {
        $query = $this->model->newQuery()->with('subject')->orderBy('id', 'desc');

        if ($request && $request->getKeyword()) {
            $query->whereILike('code', $request->getKeyword());
            $query->orWhereHas('subject', function ($q) use ($request) {
                $q->whereILike('name', $request->getKeyword());
            });
        }

        return $query;
    }
    
    /**
     * getListItems
     *
     * @return array
     */
    public function getListItems(): array
    {
        // return $this->model->get()->toArray();
        return DB::table('learning_modules')->whereNull('deleted_time')->orWhere('deleted_time', 0)->get()->toArray();
    }
}
