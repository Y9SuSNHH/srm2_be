<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\Student;

use App\Eloquent\Student;
use App\Helpers\Traits\ThrowIfNotAble;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ReflectionException;

class StudentRepository implements StudentRepositoryInterface
{
    use ThrowIfNotAble;

    private \App\Eloquent\Model|Builder|\Illuminate\Database\Eloquent\Model $query;
    private string $model;

    public function __construct()
    {
        $this->query = Student::query()->getModel();
        $this->model = Student::class;
    }

    /**
     * @return Collection
     */
    public function getBasicInformation(array $criterias = []): Collection
    {
        $query = Student::query()
            ->with([
                'classrooms:id,code',
                'studentProfile' => function ($query) {
                    /** @var Builder $query */
                    $query->with('profile:id,firstname,lastname,gender,birthday,borned_year')
                        ->select(['id', 'profile_id']);
                }
            ]);

        if (isset($criterias['classroom'])) {
            $query->whereHas('classrooms', fn($query) => $query->where('classrooms.id', $criterias['classroom']));
        } elseif (isset($criterias['classrooms'])) {
            $query->whereHas('classrooms', fn($query) => $query->whereIn('classrooms.id', (array)$criterias['classrooms']));
        }

        return $query->get([
            'id',
            'student_profile_id',
            'student_code',
            'account',
            'email',
            'profile_status',
            'student_status',
        ]);

    }

    /**
     * @param array $classrooms
     * @param Carbon $date
     * @return Collection
     */
    public function getContestStudentList(array $classrooms, Carbon $date): Collection
    {
        return Student::query()
            ->with([
                'classrooms'     => function ($query) use ($classrooms) {
                    /** @var Builder $query */
                    $query->whereIn('classrooms.id', $classrooms)
                        ->select(['classrooms.id', 'code']);
                },
                'studentProfile' => function ($query) {
                    /** @var Builder $query */
                    $query->with('profile:id,firstname,lastname,gender,birthday,borned_year')
                        ->select(['id', 'profile_id']);
                }
            ])
            ->whereExists(function ($query) use ($classrooms, $date) {
                /** @var Builder $query */
                $query->select(['student_classrooms.id'])->from('student_classrooms')
                    ->join('classrooms', 'classrooms.id', '=', 'student_classrooms.classroom_id')
                    ->whereRaw('student_classrooms.student_id = students.id')
                    ->whereIn('classrooms.id', $classrooms)
                    ->whereDate('student_classrooms.began_at', '<=', $date)
                    ->where(function ($query) use ($date) {
                        /** @var Builder $query */
                        $query->orWhereNull('student_classrooms.ended_at')
                            ->orWhereDate('student_classrooms.ended_at', '>=', $date);
                    });
            })
            ->get([
                'students.id',
                'student_profile_id',
                'student_code',
                'account',
                'email',
                'profile_status',
                'student_status',
            ]);
    }

    /**
     * @param $student_codes
     * @return Collection
     */
    public function findExistedStudents($student_codes)
    {
        $students = Student::query()->with([
            'classrooms'     => function ($query) {
                /** @var Builder $query */
                $query->select(['classrooms.id', 'code']);
            },
            'studentProfile' => function ($query) {
                /** @var Builder $query */
                $query->with('profile:id,firstname,lastname,gender,birthday,borned_year')
                    ->select(['id', 'profile_id']);
            }
        ])
            ->whereIn('student_code', $student_codes)
            ->get([
                'students.id',
                'student_profile_id',
                'student_code',
                'account',
                'email',
                'profile_status',
                'student_status',
            ]);

        return $students;
    }

    /**
     * @param int $handover_id
     * @param array $get
     * @return Collection|array
     */
    public function getAllByStudentProfileHandoverId(int $handover_id, array $get = ['*']): Collection|array
    {
        return $this->query->newQuery()->whereHas('studentProfile', function ($q) use ($handover_id) {
            $q->where('handover_id', $handover_id);
        })->get($get);
    }

    /**
     * @param array $ids
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function updateByIds(array $ids, array $data): mixed
    {
        return $this->updateAble($this->model, function () use ($ids, $data) {
            return $this->query->newQuery()->whereIn('id', $ids)->update($data);
        });
    }

    /**
     * @param int $id
     * @param array $get
     * @return array|Model|Collection|Builder|\App\Eloquent\Model|null
     */
    public function getById(int $id, array $get = ['*']): array|Model|Collection|Builder|\App\Eloquent\Model|null
    {
        return $this->query->newQuery()->select($get)->with('studentProfile:id,handover_id')->find($id);
    }

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function updateById(int $id, array $data): mixed
    {
        return $this->updateAble($this->model, function () use ($id, $data) {
            return $this->query->newQuery()->findOrFail($id)->update($data);
        });
    }

    public function getAllByStudentProfileId(array $student_profile_ids, array $get = ['*'])
    {
        return DB::table('students')->whereIn('student_profile_id', $student_profile_ids)->get($get);
    }
}
