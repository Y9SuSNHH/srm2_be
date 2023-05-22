<?php

namespace App\Http\Domain\Api\Repositories\User;

use App\Eloquent\User as EloquentUser;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\Api\Models\Staff\Staff as StaffModel;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Database\Eloquent\Builder;

class UserRepository implements UserRepositoryInterface
{
    use ThrowIfNotAble;

    private $eloquent_model;

    public function __construct()
    {
        $this->eloquent_model = EloquentUser::query()->getModel();
    }

    public function getAll(BaseSearchRequest $request): array
    {
        $query = $this->eloquent_model->with([
            'permissions:id',
            'roles' => function ($query) {
                /** @var Builder $query */
                $query->with('permissions:id')
                    ->select(['id']);
            }
        ]);

        if ($request->keyword) {
            $query->whereILike('username', $request->getKeyword());
        }

        return $query->get(['id', 'username'])->toArray();
    }

    public function getStaff(int $user_id): ?StaffModel
    {
        $this->eloquent_model->load('staff');

    }

    /**
     * @param array $attribute
     * @return array
     */
    public function create(array $attribute): array
    {
        return $this->createAble(EloquentUser::class, function () use ($attribute) {
            $user = $this->eloquent_model->create($attribute);
            return $user->toArray();
        });
    }

    /**
     * @param int $id
     * @param array $attribute
     * @return bool
     * @throws \Throwable
     */
    public function update(int $id, array $attribute): bool
    {
        return $this->updateAble(EloquentUser::class, function () use ($id, $attribute) {
            return $this->eloquent_model->newQuery()->findOrFail($id)->updateOrFail($attribute);
        });
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->deleteAble(EloquentUser::class, function () use ($id) {
            return (bool)$this->eloquent_model->newQuery()->where('id', $id)->delete();
        });
    }
}
