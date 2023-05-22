<?php
namespace App\Http\Domain\Student\Repositories\PetitionFlow;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface PetitionFlowRepositoryInterface
{
    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): mixed;

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data): mixed;

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed;

    /**
     * @param int $petition_id
     * @return mixed
     */
    public function deleteByPetitionId(int $petition_id): mixed;
}