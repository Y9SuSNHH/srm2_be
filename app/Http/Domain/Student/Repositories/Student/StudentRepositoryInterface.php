<?php

namespace App\Http\Domain\Student\Repositories\Student;

use App\Http\Domain\Student\Requests\Student\SearchRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use App\Http\Domain\Student\Models\Student\Student as StudentModel;
use ReflectionException;
use Throwable;

interface StudentRepositoryInterface
{

    /**
     * @param SearchRequest $request
     * @param bool $get_all
     * @return \App\Eloquent\Model[]|LengthAwarePaginator|Builder[]|Collection|Model[]|mixed|object
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function getAll(SearchRequest $request, bool $get_all = false): mixed;

    /**
     * @param int $id
     * @return StudentModel
     */
    public function getById(int $id): StudentModel;

    /**
     * @param int $id
     * @return Collection|array
     */
    public function getGradesById(int $id): Collection|array;

    /**
     * @param int $id
     * @return array
     * @throws ReflectionException
     */
    public function getTuitionById(int $id): array;

    /**
     * @param int $id
     * @return Collection|array
     */
    public function getProfile(int $id): Collection|array;

    /**
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ReflectionException
     * @throws Throwable
     */
    public function update(int $id, array $data): bool;

    /**
     * getListItems
     *
     * @return array
     */
    public function getListItems(): array;


    /**
     * @param array $student_codes
     * @param array $get
     * @return array|Collection
     */
    public function getByStudentCode(array $student_codes, array $get = ['*']): array|Collection;

    public function getByStudentProfileId(int $student_profile_id);
}
