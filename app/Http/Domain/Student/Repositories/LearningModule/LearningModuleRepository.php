<?php

namespace App\Http\Domain\Student\Repositories\LearningModule;

use App\Eloquent\LearningModule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LearningModuleRepository implements LearningModuleRepositoryInterface
{
    private Builder $query;
    private string $model;

    public function __construct()
    {
        $this->model = LearningModule::class;
        $this->query = LearningModule::query()->clone();
    }


    /**
     * @param array $code
     * @param array $get
     * @return Collection|array
     */
    public function getByCodes(array $code, array $get = ['*']): Collection|array
    {
        return $this->query->whereIn('code', $code)->get($get);
    }

}