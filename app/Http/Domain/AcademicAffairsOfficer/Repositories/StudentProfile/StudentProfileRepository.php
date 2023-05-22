<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\StudentProfile;

use App\Eloquent\StudentProfile;
use App\Helpers\Traits\ThrowIfNotAble;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ReflectionException;

class StudentProfileRepository implements StudentProfileRepositoryInterface
{
    use ThrowIfNotAble;

    private \App\Eloquent\Model|Builder|Model $query;
    private string $model;

    public function __construct()
    {
        $this->query = StudentProfile::query()->getModel();
        $this->model = StudentProfile::class;
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
            return $this->query->newQuery()->whereIn('id', $ids)
                ->update($data);
        });
    }

    /**
     * @param int $handover_id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function updateByHandoverId(int $handover_id, array $data): mixed
    {
        return $this->updateAble($this->model, function () use ($handover_id, $data) {
            return $this->query->newQuery()->where('handover_id', $handover_id)
                ->update($data);
        });
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

    /**
     * @param int $id
     * @param array $get
     * @return array|Model|Collection|Builder|\App\Eloquent\Model|null
     */
    public function getById(int $id, array $get = ['*']): array|Model|\Illuminate\Database\Eloquent\Collection|Builder|\App\Eloquent\Model|null
    {
        return $this->query->newQuery()->select($get)->with(['student:id,student_profile_id'])->find($id);
    }
}