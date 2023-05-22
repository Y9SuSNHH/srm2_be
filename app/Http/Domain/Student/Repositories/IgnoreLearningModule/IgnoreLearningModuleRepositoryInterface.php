<?php

namespace App\Http\Domain\Student\Repositories\IgnoreLearningModule;

use App\Http\Domain\Student\Requests\IgnoreLearningModule\SearchRequest;
use Illuminate\Database\Eloquent\Collection;
use ReflectionException;

interface IgnoreLearningModuleRepositoryInterface
{

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function getAll(SearchRequest $request): mixed;


    /**
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function insert(array $data): mixed;


    /**
     * @param int $id
     * @return mixed
     * @throws ReflectionException
     */
    public function deletedById(int $id): mixed;


    /**
     * @param array $learning_module_ids
     * @param array $get
     * @return Collection|array
     */
    public function getByLearningModuleIds(array $learning_module_ids = [], array $get = ['*']): Collection|array;
}