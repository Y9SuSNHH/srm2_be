<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\MajorObjectMap;

use App\Eloquent\Major;
use App\Eloquent\MajorObjectMap;
use App\Http\Domain\TrainingProgramme\Models\MajorObjectMap\MajorObjectMap as MajorObjectMapModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Domain\TrainingProgramme\Requests\MajorObjectMap\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class MajorObjectMapRepository implements MajorObjectMapRepositoryInterface
{
    /** @var Builder|\Illuminate\Database\Eloquent\Model */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = MajorObjectMap::query()->getModel();
    }

    /**
     * @param int $area_id
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function getMajorAndObject(int $area_id): mixed
    {
        return $this->model->newQuery()->with([
            'major' => function($query) use ($area_id) {
                $query->where('area_id', $area_id);
            },
            'enrollment_object'
        ])
            ->whereHas('major', function ($query) use ($area_id) {
                $query->where('majors.area_id', $area_id);
            })
            ->get();
    }

    /**
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator
    {
        $query = $this->majorObjectMapRepositoryQuery($request);
        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        $paginate->getCollection()->transform(function ($major_object_map) {
            return new MajorObjectMapModel($major_object_map);
        });
        return $paginate;
    }

    /**
     * @param SearchRequest $request
     * @return array
     */
    public function options(SearchRequest $request): array
    {
        return $this->majorObjectMapRepositoryQuery($request)
            ->get()
            ->transform(function ($major_object_map) {
                return new MajorObjectMapModel($major_object_map);
            })
            ->toArray();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        $major_object_map = MajorObjectMap::query()->findOrFail($id);
        return (array)new MajorObjectMapModel($major_object_map);
    }

    /**
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array
    {
        try {
            $validator['school_id'] = school()->getId();
            $major_object_map = $this->model->create($validator);
            return (array)new MajorObjectMapModel($major_object_map);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    // /**
    //  * @param int $id
    //  * @param array $validator
    //  * @return array
    //  */
    // public function update(int $id, array $validator): array
    // {
    //     try {
    //         $major = MajorObjectMap::query()
    //             ->where('id', $id)
    //             ->whereNotExists(function ($q) {
    //                 $q->select('id')->from('training_programs')->whereRaw('major_id = major_object_maps.id');
    //             })
    //             ->whereNotExists(function ($q) {
    //                 $q->select('id')->from('classrooms')->whereRaw('major_id = major_object_maps.id');
    //             })
    //             ->whereNotExists(function ($q) {
    //                 $q->select('id')->from('student_profiles')->whereRaw('major_id = major_object_maps.id');
    //             })
    //             ->first();

    //         if (!$major) {
    //             throw new \Exception("Sửa ngành học #{$id} thất bại, do ngành học đang được sử dụng");
    //         }
    //         $major_object_map = MajorObjectMap::query()->findOrFail($id);
    //         $major_object_map->update($validator);
    //         return (array)new MajorObjectMapModel($major_object_map);
    //     } catch (\Exception $e) {
    //         throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
    //     }
    // }

    // /**
    //  * @param int $id
    //  * @return array
    //  */
    // public function delete(int $id): array
    // {
    //     try {
    //         $major = MajorObjectMap::query()
    //             ->where('id', $id)
    //             ->whereNotExists(function ($q) {
    //                 $q->select('id')->from('training_programs')->whereRaw('major_id = major_object_maps.id');
    //             })
    //             ->whereNotExists(function ($q) {
    //                 $q->select('id')->from('classrooms')->whereRaw('major_id = major_object_maps.id');
    //             })
    //             ->whereNotExists(function ($q) {
    //                 $q->select('id')->from('student_profiles')->whereRaw('major_id = major_object_maps.id');
    //             })
    //             ->first();

    //         if (!$major) {
    //             throw new \Exception("Xóa ngành học #{$id} thất bại, do ngành học đang được sử dụng");
    //         }
    //         MajorObjectMap::query()->findOrFail($id)->delete();
    //         return (array)'delete successful';
    //     } catch (\Exception $e) {
    //         throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
    //     }
    // }

    /**
     * @param SearchRequest $request
     * @return Builder|\Illuminate\Database\Eloquent\Model
     */
    private function majorObjectMapRepositoryQuery(SearchRequest $request): Builder|\Illuminate\Database\Eloquent\Model
    {
        $query = $this->model->newQuery()->with([
            'major',
            'enrollment_object',
        ])
        ->whereExists(function ($q) {
            $q->select('majors.id')->from('majors')
            ->where('school_id', school()->getId())
            ->whereRaw('major_object_maps.major_id = majors.id');
        })
        ->whereExists(function ($q) {
            $q->select('enrollment_objects.id')->from('enrollment_objects')
            ->where('school_id', school()->getId())
            ->whereRaw('major_object_maps.enrollment_object_id=enrollment_objects.id');
        })
        ->orderBy('id', 'desc');

        if ($request->major_id) {
            $query->where('major_id', $request->major_id);
        }

        if ($request->enrollment_object_id) {
            $query->where('enrollment_object_id', $request->enrollment_object_id);
        }
        return $query;
    }
}
