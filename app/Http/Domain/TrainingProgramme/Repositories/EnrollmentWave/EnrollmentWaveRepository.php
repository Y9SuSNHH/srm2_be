<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\EnrollmentWave;

use App\Eloquent\EnrollmentWave;
use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\LengthAwarePaginator;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\TrainingProgramme\Models\EnrollmentWave\EnrollmentWave as EnrollmentWaveModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Http\Domain\TrainingProgramme\Requests\EnrollmentWave\SearchRequest;

/**
 * Class EnrollmentWaveRepository
 * @package App\Http\Domain\TrainingProgramme\Repositories\EnrollmentWave
 */
class EnrollmentWaveRepository implements EnrollmentWaveRepositoryInterface
{
    use ThrowIfNotAble;

    /** @var Builder|Model */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = EnrollmentWave::query()->getModel();
    }

    /**
     * @param PaginateSearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(PaginateSearchRequest $request): LengthAwarePaginator
    {
        /** @var SearchRequest $request */
        $query = $this->enrollmentWaveRepositoryQuery($request);

        /** @var LengthAwarePaginator $paginate */
        if(!$request->perPage()) {
            $paginate = $query->makePaginate(1);
        } else {
            $paginate = $query->makePaginate($request->perPage());
        }

        $paginate->getCollection()->transform(function ($enrollment_wave) {
            return new EnrollmentWaveModel($enrollment_wave);
        });

        return $paginate;
    }

    /**
     * @param SearchRequest $request
     * @param string[] $columns
     * @return Collection
     */
    public function options(SearchRequest $request, $columns = ['*']): Collection
    {
        return $this->enrollmentWaveRepositoryQuery($request)
            ->get($columns)
            ->transform(function ($enrollment_wave) {
                return new EnrollmentWaveModel($enrollment_wave);
            });
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getById(int $id): mixed
    {
        return EnrollmentWave::query()->findOrFail($id);
    }

    /**
     * @param array $validator
     * @return EnrollmentWaveModel
     * @throws \ReflectionException
     */
    public function create(array $validator): EnrollmentWaveModel
    {
        return $this->createAble(EnrollmentWave::class, function () use ($validator) {
            $validator['school_id'] = school()->getId();
            $enrollment_wave = $this->model->create($validator);
            return new EnrollmentWaveModel($enrollment_wave);
        });
    }

    /**
     * @param int $id
     * @param array $validator
     * @return EnrollmentWaveModel
     * @throws \ReflectionException
     */
    public function update(int $id, array $validator): EnrollmentWaveModel
    {
        return $this->updateAble(EnrollmentWave::class, function () use ($id, $validator) {
            /** @var EnrollmentWave $enrollment_wave */
            $enrollment_wave = EnrollmentWave::query()->findOrFail($id);

            if ($enrollment_wave->first_day_of_school->isBefore(Carbon::now())) {
                throw_json_response('Không sửa được vì đã qua ngày khai giảng');
            }

            $enrollment_wave->update($validator);
            return new EnrollmentWaveModel($enrollment_wave);
        });
    }

    /**
     * @param int $id
     * @return bool
     * @throws \ReflectionException
     */
    public function delete(int $id): bool
    {
        return $this->deleteAble(EnrollmentWave::class, function () use ($id) {
            $enrollment_wave = EnrollmentWave::query()->withCount(['classrooms', 'studentProfiles'])->findOrFail($id);

            if ($enrollment_wave->classrooms_count) {
                throw_json_response('Không xóa được vì có lớp đang sử dụng');
            }

            if ($enrollment_wave->student_profiles_count) {
                throw_json_response('Không xóa được vì có hồ sơ sinh viên đamg sử dụng');
            }

            $enrollment_wave->delete();
            return true;
        });
    }

    /**
     * @param SearchRequest $request
     * @return Builder|Model
     */
    private function enrollmentWaveRepositoryQuery(SearchRequest $request): Builder|Model
    {
        $query = $this->model->newQuery()->with('area')->orderBy('first_day_of_school', 'desc');

        if ($request->group_number) {
            $query->where('group_number', $request->group_number);
        }

        if ($request->area_id) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->first_day_of_school) {
            $query->whereDate('first_day_of_school', $request->first_day_of_school);
        }

        if ($request->enrollment_wave_year) {
            $query->whereYear('first_day_of_school', $request->enrollment_wave_year);
        }

        return $query;
    }

    public function findEnrollmentWave(array $first_day_of_schools) {
        $enrollment_waves = $this->model->query()
                       ->whereIn('first_day_of_school', $first_day_of_schools)
                       ->get();
        return $enrollment_waves;
    }
}