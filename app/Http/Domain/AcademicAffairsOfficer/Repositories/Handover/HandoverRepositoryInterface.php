<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\Handover;

use App\Http\Domain\AcademicAffairsOfficer\Models\Handover as HandoverModel;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\SearchRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\SearchStudentRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\StoreRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\UpdateRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\UpdateStudentProfilesRequest;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use ReflectionException;
use Throwable;

interface HandoverRepositoryInterface
{
    /**
     * @param SearchRequest $request
     * @param array $select
     * @param bool $get_all
     * @return mixed
     * @throws ValidationException
     */
    public function getAll(SearchRequest $request, array $select = ['*'], bool $get_all = false): mixed;


    /**
     * @param StoreRequest $request
     * @return mixed
     * @throws ValidationException
     * @throws ReflectionException
     */
    public function store(StoreRequest $request): mixed;


    /**
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    public function updateById(int $id, array $data): mixed;

    /**
     * @param int $id
     * @return mixed
     * @throws ReflectionException
     */
    public function destroy(int $id): mixed;

    /**
     * @param int $id
     * @return int
     */
    public function getStudentProfilesCount(int $id): int;

    /**
     * @param int $id
     * @param SearchStudentRequest $request
     * @return array|HandoverModel
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function getByIdWithStudentProfiles(int $id, SearchStudentRequest $request): array|HandoverModel;



    /**
     * @param int $id
     * @return mixed
     */
    public function getWithCountStudentProfile(int $id): mixed;



    /**
     * @param int $id
     * @param bool $is_in_handover
     * @param array $get
     * @return array|Collection
     */
    public function getStudentProfileIdInHandover(int $id, bool $is_in_handover = false, array $get = ['*'], ): array|Collection;


    /**
     * @param int $id
     * @return mixed
     */
    public function getIsLockById(int $id): mixed;

    public function getById(int $id, array $get = ['*']);

    public function getStudentById(int $id, BaseSearchRequest $request);
}