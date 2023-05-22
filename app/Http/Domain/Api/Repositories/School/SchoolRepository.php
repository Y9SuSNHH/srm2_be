<?php

namespace App\Http\Domain\Api\Repositories\School;

use App\Eloquent\School;
use App\Helpers\Interfaces\ThrowIfNotAbleInterface;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Api\Models\School\School as SchoolModel;
use App\Http\Domain\Api\Requests\School\CreateSchoolRequest;
use App\Http\Domain\Api\Requests\School\UpdateSchoolRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class SchoolRepository
 * @package App\Http\Domain\Api\Repositories\School
 */
class SchoolRepository implements SchoolRepositoryInterface, ThrowIfNotAbleInterface
{
    use ThrowIfNotAble;

    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = School::query()->getModel();
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $query = $this->model->orderByDesc('priority')->get()
            ->transform(function ($school) {
                return new SchoolModel($school);
            })->toArray();
    }

    /**
     * @param int $id
     * @return SchoolModel
     */
    public function getById(int $id): SchoolModel
    {
        $school = $this->model->findOrFail($id);
        return new SchoolModel($school);
    }

    /**
     * @param CreateSchoolRequest $request
     * @return SchoolModel
     */
    public function create(CreateSchoolRequest $request): SchoolModel
    {
        return $this->createAble(School::class, function () use ($request) {
            $school = $this->model->create($request->validated());
            return new SchoolModel($school);
        });
    }

    /**
     * @param int $id
     * @param UpdateSchoolRequest $request
     * @return SchoolModel
     */
    public function update(int $id, UpdateSchoolRequest $request): SchoolModel
    {
        $this->updateAble(School::class, function () use ($id, $request) {
            $school = $this->model->findOrFail($id);
            $school->update($request->validated());
            return new SchoolModel($school);
        });
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete(int $id): bool
    {
        throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => 'Can not delete school']));
//        try {
//            if (!auth()->guard()->deletable(School::class)) {
//                throw new \Exception("You don't have permission to delete record for this model: ".School::class);
//            }
//
//            $school = $this->model
//                ->where('id', $id)
//                ->whereNotExists(function ($q) { $q->select('id')->from('areas')->whereRaw('school_id = schools.id'); })
//                ->whereNotExists(function ($q) { $q->select('id')->from('majors')->whereRaw('school_id = schools.id'); })
//                ->whereNotExists(function ($q) { $q->select('id')->from('enrollment_waves')->whereRaw('school_id = schools.id'); })
//                ->whereNotExists(function ($q) { $q->select('id')->from('areas')->whereRaw('school_id = schools.id'); })
//                ->first();
//
//            if (!$school) {
//                throw new \Exception("Delete school #{$id} fail");
//            }
//
//            $school->delete();
//            return true;
//        } catch (\Exception $e) {
//            activity_history()->note('fail');
//            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
//        }
    }
}
