<?php


namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\Student;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ReflectionException;

interface StudentRepositoryInterface
{

    /**
     * @param int $handover_id
     * @param array $get
     * @return Collection|array
     */
    public function getAllByStudentProfileHandoverId(int $handover_id, array $get = ['*']): Collection|array;


    /**
     * @param array $ids
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function updateByIds(array $ids, array $data): mixed;


    /**
     * @param int $id
     * @param array $get
     * @return array|Model|Collection|Builder|\App\Eloquent\Model|null
     */
    public function getById(int $id, array $get = ['*']): array|Model|Collection|Builder|\App\Eloquent\Model|null;

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     */
    public function updateById(int $id, array $data): mixed;


    public function getAllByStudentProfileId(array $student_profile_ids, array $get = ['*']);

}