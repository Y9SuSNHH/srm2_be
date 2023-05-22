<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\StudySession;

use App\Eloquent\Period;
use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\Interfaces\ThrowIfNotAbleInterface;
use App\Helpers\Traits\ThrowIfNotAble;

class PeriodRepository implements PeriodRepositoryInterface, ThrowIfNotAbleInterface
{
    use ThrowIfNotAble;

    /**
     * @var \App\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    private $eloquent_model;

    public function __construct()
    {
        $this->eloquent_model = Period::query()->getModel();
    }

    public function getAll(PaginateSearchRequest $request): \App\Helpers\LengthAwarePaginator
    {
        // TODO: Implement getAll() method.
    }

    public function getById(int $id): mixed
    {
        // TODO: Implement getById() method.
    }

    public function create(array $attribute): mixed
    {
        // TODO: Implement create() method.
    }

    public function update(int $id, array $attribute): mixed
    {
        // TODO: Implement update() method.
    }

    public function delete(int $id): bool
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return array
     */
    public function getExistSemester(): array
    {
        return $this->eloquent_model->newQuery()->get(['classroom_id', 'semester'])->transform(function ($item) {
            return [$item->classroom_id ?? 0, $item->semester ?? 0];
        })->toArray();
    }

    public function insert(array $attribute): bool
    {
        return $this->createAble($this->eloquent_model::class, function () use ($attribute) {
            return $this->eloquent_model->insert($attribute);
        });
    }
}
