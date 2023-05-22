<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom;

use App\Eloquent\Classroom as EloquentClassroom;
use App\Http\Domain\AcademicAffairsOfficer\Models\Classroom\Classroom;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom\SearchRequest;
use App\Http\Enum\StaffTeam;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class ClassRepository
 * @package App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom
 */
class ClassRepository implements ClassRepositoryInterface
{
    /** @var Builder|\Illuminate\Database\Eloquent\Model|EloquentClassroom */
    private $eloquent_model;

    public function __construct()
    {
        $this->eloquent_model = EloquentClassroom::query()->getModel();
    }

    /**
     * @param array $ids
     * @return Collection
     */
    public function findAll(array $ids): Collection
    {
        return $this->eloquent_model->with(['area:id,code'])->findMany($ids);
    }

    /**
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator
    {
        $query = $this->classroomQuery($request);
        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        $paginate->getCollection()->transform(function ($classroom) {
            return new Classroom($classroom);
        });

        return $paginate;
    }

    /**
     * @param SearchRequest $request
     * @param array $columns
     * @return mixed
     */
    public function options(SearchRequest $request, array $columns = ['*']): mixed
    {
        $query = $this->classroomQuery($request);

        return $query->select($columns)->distinct()->get()->mapInto(Classroom::class);
    }

    /**
     * @param int $id
     * @return Classroom
     */
    public function getById(int $id): Classroom
    {
        /** @var EloquentClassroom $classroom */
        $classroom = $this->eloquent_model
            ->with([
                'major:id,name',
                'enrollmentObject:id,name',
                'area:id,name',
                'enrollmentWave:id,first_day_of_school',
                'learningManagement:id,fullname',
            ])
            ->findOrFail($id);

        return new Classroom($classroom);
    }

    /**
     * @param array $attribute
     * @return Classroom
     * @throws HttpResponseException
     */
    public function create(array $attribute): Classroom
    {
        try {
            if (!auth()->guard()->creatable(EloquentClassroom::class)) {
                throw new \Exception("You don't have permission to create record for this model: ".EloquentClassroom::class);
            }

            $attribute['school_id'] = school()->getId();
            $classroom = $this->eloquent_model->create($attribute);
            return new Classroom($classroom);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param int $id
     * @param array $attribute
     * @return Classroom
     * @throws HttpResponseException
     */
    public function update(int $id, array $attribute): Classroom
    {
        try {
            if (!auth()->guard()->editable(EloquentClassroom::class)) {
                throw new \Exception("You don't have permission to edit record for this model: ".EloquentClassroom::class);
            }

            $classroom = $this->eloquent_model->findOrFail($id);
            $classroom->update(array_replace(['staff_id' => null], $attribute));
            return new Classroom($classroom);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            if (!auth()->guard()->deletable(EloquentClassroom::class)) {
                throw new \Exception("You don't have permission to delete record for this model: ".EloquentClassroom::class);
            }

            $classroom = $this->eloquent_model->findOrFail($id);

            if ($classroom->students->count()) {
                throw new \Exception('Xóa lớp thất bại, vì còn có sinh viên trong lớp');
            }

            return $classroom->delete();
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes): bool
    {
        try {
            if (!auth()->guard()->creatable(EloquentClassroom::class)) {
                throw new \Exception("You don't have permission to create record for this model: ".EloquentClassroom::class);
            }

            $now = Carbon::now();
            $attributes = array_map(function ($item) use ($now) {
                return array_merge($item, [
                    'school_id' => school()->getId(),
                    'created_by' => auth()->getId(),
                    'created_at' => $now,
                ]);
            }, $attributes);

            return $this->eloquent_model->insert($attributes);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param array $ids
     * @param array $attribute
     * @return bool|int
     */
    public function updateMultipleRecords(array $ids, array $attribute): bool|int
    {
        try {
            if (!auth()->guard()->editable(EloquentClassroom::class)) {
                throw new \Exception("You don't have permission to edit record for this model: ".EloquentClassroom::class);
            }

            $attribute['updated_by'] = auth()->getId();
            $attribute['updated_at'] = Carbon::now();

            return $this->eloquent_model->whereIn('id', $ids)->update($attribute);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param int $area_id
     * @return array
     */
    public function getCodeExists(int $area_id): array
    {
        return $this->eloquent_model->newQuery()
            ->where('area_id', $area_id)
            ->pluck('code', 'code')
            ->toArray();
    }

    /**
     * @param SearchRequest $request
     * @return Builder|\Illuminate\Database\Eloquent\Model
     */
    private function classroomQuery(SearchRequest $request): Builder|\Illuminate\Database\Eloquent\Model
    {
        $query = $this->eloquent_model->newQuery()
            ->with([
                'major:id,name',
                'enrollmentObject:id,name,code,shortcode,classification',
                'area:id,name',
                'enrollmentWave:id,first_day_of_school',
                'learningManagement:id,fullname',
            ])
            ->withCount('students')
            ->orderBy('id');

        if ($request->major_id) {
            $query->where('major_id', $request->major_id);
        }

        if ($request->area_id) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->first_day_of_school) {
            $query->whereExists(function ($query) use ($request) {
                /** @var Builder $query */
                $query->select('id')
                    ->from('enrollment_waves')
                    ->whereDate('first_day_of_school', $request->first_day_of_school)
                    ->whereRaw('enrollment_waves.id=classrooms.enrollment_wave_id');
            });
        }

        if ($request->staff_id) {
            if ($request->staff_id > 0) {
                $query->where('staff_id', $request->staff_id);
            } else {
                $query->whereNotExists(function ($query) use ($request) {
                    /** @var Builder $query */
                    $query->select('id')->from('staffs')
                        ->whereRaw('staffs.id=classrooms.staff_id')
                        ->whereRaw('(staffs.deleted_time IS NULL OR staffs.deleted_time=0)')
                        ->where('team', StaffTeam::LEARNING_MANAGEMENT);
                });
            }
        }

        if ($request->keyword) {
            $query->where('code', 'ilike', '%' . $request->keyword . '%');
        }
        return $query;
    }

    public function findClassroomsByCodes($codes) {
        $classrooms = $this->eloquent_model->query()
                                            ->with(['major:id,shortcode', 
                                                    'enrollmentObject:id,shortcode,classification',
                                                    'area:id,code',
                                                    'enrollmentWave:id,first_day_of_school'])
                                            ->whereIn('code',$codes)
                                            ->get();
        return $classrooms;
    }
}
