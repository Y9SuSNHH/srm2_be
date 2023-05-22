<?php

namespace App\Http\Domain\Api\Controllers;

use App\Http\Domain\Api\Repositories\School\SchoolRepositoryInterface;
use App\Http\Domain\Api\Requests\School\CreateSchoolRequest;
use App\Http\Domain\Api\Requests\School\UpdateSchoolRequest;
use Laravel\Lumen\Routing\Controller;

/**
 * Class SchoolController
 * @package App\Http\Domain\Api\Controllers
 */
class SchoolController extends Controller
{
    /**
     * @param SchoolRepositoryInterface $school_repository
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(SchoolRepositoryInterface $school_repository): \Illuminate\Http\JsonResponse
    {
        return json_response(true, $school_repository->getAll());
    }

    /**
     * @param int $id
     * @param SchoolRepositoryInterface $school_repository
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id, SchoolRepositoryInterface $school_repository): \Illuminate\Http\JsonResponse
    {
        $school = $school_repository->getById($id);
        return json_response(true, $school);
    }

    /**
     * @param CreateSchoolRequest $request
     * @param SchoolRepositoryInterface $school_repository
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function create(CreateSchoolRequest $request, SchoolRepositoryInterface $school_repository): \Illuminate\Http\JsonResponse
    {
        activity_history()->create($request);
        $request->throwJsonIfFailed();
        $data = $school_repository->create($request);
        activity_history()->note(['add new school', $data])->append();
        return json_response(true, $data);
    }

    /**
     * @param int $id
     * @param UpdateSchoolRequest $request
     * @param SchoolRepositoryInterface $school_repository
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(int $id, UpdateSchoolRequest $request, SchoolRepositoryInterface $school_repository): \Illuminate\Http\JsonResponse
    {
        activity_history()->update(['request' => $request, 'id' => $id]);
        $request->throwJsonIfFailed();
        $data = $school_repository->update($id, $request);
        activity_history()->note($data)->append();
        return json_response(true, $data);
    }

    /**
     * @param int $id
     * @param SchoolRepositoryInterface $school_repository
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete(int $id, SchoolRepositoryInterface $school_repository): \Illuminate\Http\JsonResponse
    {
        activity_history()->delete($id);
        $school_repository->delete($id);
        activity_history()->note('done');
        return json_response(true);
    }
}
