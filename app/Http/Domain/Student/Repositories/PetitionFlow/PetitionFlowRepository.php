<?php

namespace App\Http\Domain\Student\Repositories\PetitionFlow;

use App\Eloquent\PetitionFlow;
use App\Helpers\Interfaces\ThrowIfNotAbleInterface;
use App\Helpers\Traits\ThrowIfNotAble;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Domain\Student\Models\PetitionFlow as PetitionFlowModel;

class PetitionFlowRepository implements PetitionFlowRepositoryInterface, ThrowIfNotAbleInterface
{
    use ThrowIfNotAble;

    private Builder $model_eloquent;
    private string $model;

    public function __construct()
    {
        $this->model          = PetitionFlow::class;
        $this->model_eloquent = PetitionFlow::query();
    }

    /**
     * @param array $data
     * @return mixed
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
     */
    public function update(int $id, array $data): mixed
    {
        return $this->updateAble($this->model, function () use ($id, $data) {
            return $this->model_eloquent->clone()->findOrFail($id)->updateOrFail($data);
        });
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        return $this->deleteAble($this->model, function () use ($id) {
            return $this->model_eloquent->clone()->findOrFail($id)->delete();
        });
    }

    /**
     * @param int $petition_id
     * @return mixed
     */
    public function deleteByPetitionId(int $petition_id): mixed
    {
        return $this->deleteAble($this->model, function () use ($petition_id) {
            return $this->model_eloquent->clone()->where('petition_id', $petition_id)->delete();
        });
    }
}