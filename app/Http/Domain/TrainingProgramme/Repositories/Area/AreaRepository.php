<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Area;


use App\Eloquent\Area;
use App\Eloquent\Classroom;
use App\Eloquent\EnrollmentWave;
use App\Eloquent\Major;
use App\Eloquent\School;
use App\Http\Domain\TrainingProgramme\Models\Area\Area as AreaModel;
use App\Http\Domain\TrainingProgramme\Models\School\School as SchoolModel;
use App\Http\Domain\TrainingProgramme\Requests\Area\CreateAreaRequest;
use App\Http\Domain\TrainingProgramme\Requests\Area\UpdateAreaRequest;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;


/**
 * Class AreaRepository
 * @package App\Http\Domain\TrainingProgramme\Repositories\Area
 */
class AreaRepository implements AreaRepositoryInterface
{
    /**
     * @var Builder|Model
     */
    private $model_eloquent;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model_eloquent = Area::query()->getModel();
    }

    /**
     * @return \Illuminate\Support\Collection|\Illuminate\Support\Enumerable
     */
    public function getAll()
    {
        return $this->model_eloquent->get()->mapInto(AreaModel::class);
    }

    /**
     * @param int $id
     * @return AreaModel
     */
    public function getById(int $id): AreaModel
    {
        $area = $this->model_eloquent->findOrFail($id)->toArray();
        return new AreaModel($area);
    }

    /**
     * @param CreateAreaRequest $request
     * @return AreaModel
     */
    public function create(CreateAreaRequest $request): AreaModel
    {
//        try {
//            if (!auth()->guard()->creatable(AreaModel::class)) {
//                throw new \Exception("You don't have permission to create record for this model: ".SchoolModel::class);
//            }
//
//            $school = $this->model_eloquent->create($request->validated());
//            return new AreaModel($school);
//        } catch (\Exception $e) {
//            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
//        }
        try {
            if (!auth()->guard()->creatable(Area::class)) {
                throw new \Exception("You don't have permission to create record for this model: ".Area::class);
            }

            $area = $this->model_eloquent->create(['school_id' => \school()->getId(), ...$request->validated()])->toArray();
            return new AreaModel($area);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param int $id
     * @param UpdateAreaRequest $request
     * @return AreaModel
     */
    public function update(int $id, UpdateAreaRequest $request): AreaModel
    {
        try {
            Area::query()->where('id', $id)->update($request->all());
            $area = Area::query()->findOrFail($id)->toArray();
            return new AreaModel($area);
        } catch (\Exception $e) {
            return (array)$e->getMessage();
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        if (Major::query()->where('area_id', $id)->exists())
        {
            return (array)'this area is using in major table';
        }
        if (EnrollmentWave::query()->where('area_id', $id)->exists())
        {
            return (array)'this area is using in enrollment wave table';
        }
        if (Classroom::query()->where('area_id', $id)->exists())
        {
            return (array)'this area is using in class table';
        }
        try {
            $are = Area::query()->findOrFail($id);
            $are->delete();
            return (array)'delete successful';
        } catch (Exception $e) {
            return (array)$e->getMessage();
        }
    }

    public function findAreaByCodes(array $codes) {
        $areas = $this->model_eloquent->query()
                                      ->whereIn('code',$codes)
                                      ->get();
        return $areas;
    }
}
