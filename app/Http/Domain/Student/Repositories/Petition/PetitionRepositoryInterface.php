<?php

namespace App\Http\Domain\Student\Repositories\Petition;

use App\Eloquent\Model as EloquentModel;
use App\Http\Domain\Student\Models\Petition as PetitionModel;
use App\Http\Domain\Student\Requests\Petition\SearchRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use ReflectionException;

interface PetitionRepositoryInterface
{
    /**
     * @param SearchRequest $request
     * @param bool $is_get_all
     * @return mixed
     * @throws ValidationException
     */
    public function getAll(SearchRequest $request, bool $is_get_all = false): mixed;

    /**
     * @param int $id
     * @return PetitionModel
     * @throws ReflectionException
     */
    public function getById(int $id): PetitionModel;

    /**
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function create(array $data): mixed;

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function update(int $id, array $data): mixed;

    /**
     * @param int $id
     * @return mixed
     * @throws ReflectionException
     */
    public function delete(int $id): mixed;

    /**
     * @param int $id
     * @return array|Model|Collection|Builder|EloquentModel|null
     */
    public function getWithLatestPetitionFlow(int $id): array|Model|Collection|Builder|EloquentModel|null;
}