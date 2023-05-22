<?php

namespace App\Http\Domain\Student\Repositories\Profile;

use App\Eloquent\Profile;
use App\Helpers\Traits\ThrowIfNotAble;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProfileRepository implements ProfileRepositoryInterface
{
    use ThrowIfNotAble;

    private string $model;
    private Profile $model_eloquent;

    public function __construct()
    {
        $this->model          = Profile::class;
        $this->model_eloquent = Profile::getModel();
    }

    /**
     * @param int $id
     * @return Model|Collection|Builder|array|null
     */
    public function getById(int $id): Model|Collection|Builder|array|null
    {
        return $this->model_eloquent->newQuery()->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        return $this->model_eloquent->newQuery()->findOrFail($id)->updateOrFail($data);
        // return $this->updateAble($this->model, function () use ($id, $data) {
        //     return $this->model_eloquent->newQuery()->findOrFail($id)->updateOrFail($data);
        // });
    }
}