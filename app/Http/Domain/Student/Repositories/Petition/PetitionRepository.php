<?php

namespace App\Http\Domain\Student\Repositories\Petition;

use App\Eloquent\Model as EloquentModel;
use App\Eloquent\Petition;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Student\Models\Petition as PetitionModel;
use App\Http\Domain\Student\Requests\Petition\SearchRequest;
use App\Http\Enum\RoleAuthority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ReflectionException;

class PetitionRepository implements PetitionRepositoryInterface
{
    use ThrowIfNotAble;

    protected string $model;
    protected Builder|Model $model_eloquent;

    public function __construct()
    {
        $this->model          = Petition::class;
        $this->model_eloquent = Petition::query()->getModel();
    }


    /**
     * @param SearchRequest $request
     * @param bool $is_get_all
     * @return mixed
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function getAll(SearchRequest $request, bool $is_get_all = false): mixed
    {
        $validated = $request->validated();
        $query     = $this->model_eloquent->clone()
            ->with([
                'student'      => function ($q) {
                    $q->select('students.id', 'student_code', 'student_profile_id', 'account');
                    $q->with([
                        'profile:profiles.id,firstname,lastname,gender,phone_number,birthday',
                        'studentProfile:id,profile_code',
                        'classroom' => function ($q2) {
                            $q2->select('classrooms.id', 'code', 'major_id', 'staff_id', 'enrollment_wave_id', 'area_id');
                            $q2->with([
                                'major:id,code,name,shortcode',
                                'area:id,name,code',
                                'staff:id,fullname',
                                'enrollmentWave:id,first_day_of_school',
                            ]);
                        },
                    ]);
                },
                'petitionFlows' => function ($q) {
                    $q->with('staff')->latest();
                }
            ]);
        if (!empty($validated['profile_code'])) {
            $profile_code = trim(mb_strtolower($validated['profile_code']));
            $query->when($profile_code, function ($q) use ($profile_code) {
                $q->whereRelation('student.studentProfile', DB::raw("lower(profile_code)"), "LIKE", "%$profile_code%");
            });
        }
        if (!empty($validated['student_code'])) {
            $student_code = trim($validated['student_code']);
            $query->when($student_code, function ($q) use ($student_code) {
                $q->whereRelation('student', "student_code", "LIKE", "%$student_code%");
            });
        }
        if (!empty($validated['fullname'])) {
            $fullname = trim(mb_strtolower($validated['fullname']));
            $query->when($fullname, function ($q) use ($fullname) {
                $q->whereRelation('student.profile', DB::raw("lower(CONCAT(firstname, ' ', lastname))"), "LIKE", "%$fullname%");
            });
        }
        if (!empty($validated['phone_number'])) {
            $phone_number = trim($validated['phone_number']);
            $query->when($phone_number, function ($q) use ($phone_number) {
                $q->whereRelation('student.profile', 'phone_number', "LIKE", "$phone_number%");
            });
        }
        if (!empty($validated['status'])) {
            $query->whereIn('status', $validated['status']);
        }
        if (!empty($validated['staff'])) {
            $staff = $validated['staff'];
            $query->whereHas('student.classroom.staff', function ($q2) use ($staff) {
                $q2->whereIn('staffs.id', $staff);
            });
        }
        if (!empty($validated['classroom'])) {
            $classroom = $validated['classroom'];
            $query->whereHas('student.classroom', function ($q2) use ($classroom) {
                $q2->whereIn('classrooms.id', $classroom);
            });
        }
        if (RoleAuthority::LEARNING_MANAGEMENT()->check() && !RoleAuthority::PM()->check()) {
            $query->where('created_by', auth()->user()->id);
        }
        if ($is_get_all) {
            $data = $query->latest()->get()->transform(function ($student) {
                return new PetitionModel($student);
            });
        } else {
            $data = $query->latest()->makePaginate($request->perPage());
            $data->getCollection()->transform(function ($student) {
                return new PetitionModel($student);
            });
        }
        return $data;
    }

    /**
     * @param int $id
     * @return PetitionModel
     * @throws ReflectionException
     */
    public function getById(int $id): PetitionModel
    {
        $data = $this->model_eloquent->clone()->with(['petitionFlows'=> fn($q) => $q->with('staff:id,fullname')->latest()])->findOrFail($id);
        return new PetitionModel($data);
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function create(array $data): mixed
    {
        return $this->createAble($this->model, function () use ($data) {
            return $this->model_eloquent->clone()->create($data);
        });
    }


    /**
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     * @throws \Throwable
     */
    public function update(int $id, array $data): mixed
    {
        return $this->updateAble($this->model, function () use ($id, $data) {
            return $this->model_eloquent->newQuery()->findOrFail($id)->updateOrFail($data);
        });
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ReflectionException
     */
    public function delete(int $id): mixed
    {
        return $this->deleteAble($this->model, function () use ($id) {
            return $this->model_eloquent->newQuery()->findOrFail($id)->delete();
        });
    }


    /**
     * @param int $id
     * @return array|Model|Collection|Builder|EloquentModel|null
     */
    public function getWithLatestPetitionFlow(int $id): array|Model|Collection|Builder|EloquentModel|null
    {
        return $this->model_eloquent->newQuery()->with('latestPetitionFlow')->findOrFail($id);
    }

}