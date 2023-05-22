<?php

namespace App\Http\Domain\Student\Repositories\IgnoreLearningModule;

use App\Eloquent\IgnoreLearningModule;
use App\Eloquent\Student;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Student\Models\IgnoreLearningModule as IgnoreLearningModuleModel;
use App\Http\Domain\Student\Requests\IgnoreLearningModule\SearchRequest;
use App\Http\Domain\Student\Requests\IgnoreLearningModule\StoreRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ReflectionException;

class IgnoreLearningModuleRepository implements IgnoreLearningModuleRepositoryInterface
{
    use ThrowIfNotAble;

    private string $model;
    private Builder $query;

    public function __construct()
    {
        $this->model = IgnoreLearningModule::class;
        $this->query = IgnoreLearningModule::query();
    }

    /**
     * @param SearchRequest $request
     * @return mixed
     * @throws ValidationException
     */
    public function getAll(SearchRequest $request): mixed
    {
        $validated = $request->validated();
        $query     = $this->query->clone()->with([
            'learningModule' => function ($q) {
                $q->select('learning_modules.id', 'subject_id', 'code', 'amount_credit');
                $q->with('subject:subjects.id,name');
            },
            'student'        => function ($q) {
                $q->select('students.id', 'student_profile_id', 'student_code');
                $q->with([
                    'classroom' => function ($q1) {
                        $q1->select('classrooms.id', 'code', 'staff_id');
                        $q1->with('staff:id,fullname');
                    },
                    'profile:profiles.id,firstname,lastname,gender,birthday,phone_number'
                ]);
            },
            'storageFile',
        ]);
        if (!empty($validated['student_code'])) {
            $query->whereHas('student', function ($q) use ($validated) {
                if (!empty($validated['student_code'])) {
                    $q->whereILike('student_code', $validated['student_code']);
                }
            });
        }

        if (!empty($validated['fullname']) || !empty($validated['phone_number'])) {
            $query->whereHas('student.profile', function ($q) use ($validated) {
                if (!empty($validated['fullname'])) {
                    $q->whereILike(DB::raw("lower(CONCAT(profiles.firstname, ' ', profiles.lastname))"), $validated['fullname']);
                }
                if (!empty($validated['phone_number'])) {
                    $phone_number = $validated['phone_number'];
                    $q->where('phone_number', 'LIKE', "$phone_number%");
                }
            });
        }

        if (!empty($validated['profile_code'])) {
            $profile_code = mb_strtolower($validated['profile_code'], 'UTF-8');
            $query->whereHas('student.studentProfile', function ($q1) use ($profile_code) {
                $q1->whereILike(DB::raw("lower(profile_code)"), $profile_code);
            });
        }

        if (!empty($validated['staff'])) {
            $query->whereHas('student.classroom.staff', function ($q1) use ($validated) {
                $q1->whereIn('staffs.id', $validated['staff']);
            });
        }

        if (!empty($validated['first_day_of_school'])) {
            $query->whereHas('student.classroom.enrollmentWave', function ($q1) use ($validated) {
                $q1->whereDate('first_day_of_school', $validated['first_day_of_school']);
            });
        }

        if (!empty($validated['classroom'])) {
            $query->whereHas('student.classroom', function ($q1) use ($validated) {
                $q1->whereIn('classrooms.id', $validated['classroom']);
            });
        }

        if (!empty($validated['learning_module'])) {
            $query->whereHas('learningModule', function ($q1) use ($validated) {
                $q1->whereIn('learning_modules.id', $validated['learning_module']);
            });
        }

        $data = $query->orderByDesc('created_at')->makePaginate($request->perPage());

        $data->getCollection()->transform(function ($ignore_learning_module) {
            return new IgnoreLearningModuleModel($ignore_learning_module);
        });

        return $data;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function insert(array $data): mixed
    {
        return $this->createAble($this->model, function () use ($data) {
            DB::transaction(function () use ($data) {
                return $this->query->insert($data);
            });
        });
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ReflectionException
     */
    public function deletedById(int $id): mixed
    {
        return $this->deleteAble($this->model, function () use ($id) {
            $data = $this->query->clone()->findOrFail($id);
            if (!is_null($data->storage_file_id)) {
                $data = $this->query->clone()->where('storage_file_id', $data->storage_file_id);
            }
            return $data->delete();
        });
    }

    /**
     * @param array $learning_module_ids
     * @param array $get
     * @return Collection|array
     */
    public function getByLearningModuleIds(array $learning_module_ids = [], array $get = ['*']): Collection|array
    {
        return $this->query->clone()->whereIn('learning_module_id', $learning_module_ids)->get($get);
    }
}