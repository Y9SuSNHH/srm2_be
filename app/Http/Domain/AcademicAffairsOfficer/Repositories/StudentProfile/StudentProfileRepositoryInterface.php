<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\StudentProfile;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ReflectionException;

interface StudentProfileRepositoryInterface
{

    /**
     * @param array $ids
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function updateByIds(array $ids, array $data): mixed;


    /**
     * @param int $handover_id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function updateByHandoverId(int $handover_id, array $data): mixed;


    /**
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function updateById(int $id, array $data): mixed;


    /**
     * @param int $id
     * @param array $get
     * @return array|Model|Collection|Builder|\App\Eloquent\Model|null
     */
    public function getById(int $id, array $get = ['*']): array|Model|\Illuminate\Database\Eloquent\Collection|Builder|\App\Eloquent\Model|null;
}