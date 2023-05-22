<?php

namespace App\Http\Domain\Api\Controllers;

use App\Eloquent\Model;
use App\Http\Domain\Api\Repositories\Permission\PermissionRepositoryInterface;
use App\Http\Domain\Api\Requests\Permission\CreateRequest;
use App\Http\Domain\Api\Requests\Permission\UpdateRequest;
use App\Http\Domain\Api\Requests\Permission\SearchRequest;
use App\Http\Domain\Api\Services\PermissionService;

/**
 * Class PermissionController
 * @package App\Http\Domain\Api\Controllers
 */
class PermissionController
{
    /** @var PermissionRepositoryInterface */
    private $permission_repository;

    /**
     * PermissionController constructor.
     * @param PermissionRepositoryInterface $repository
     */
    public function __construct(PermissionRepositoryInterface $repository)
    {
        $this->permission_repository = $repository;
    }

    /**
     * @param SearchRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(SearchRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->permission_repository->getAll($request));
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        return json_response(true, $this->permission_repository->getById($id));
    }

    /**
     * @param PermissionService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function createForm(PermissionService $service): \Illuminate\Http\JsonResponse
    {
        return json_response(true, [
            'guardEloquent' => Model::getListEloquent(),
        ]);
    }

    /**
     * @param CreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function create(CreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->permission_repository->create($request));
    }

    /**
     * @param int $id
     * @param UpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(int $id, UpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->permission_repository->update($id, $request));
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(int $id): \Illuminate\Http\JsonResponse
    {
        return json_response(true, $this->permission_repository->delete($id));
    }

    /**
     * @param int $id
     * @param UpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function assignRole(int $id, UpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->permission_repository->assignRole($id, $request));
    }

    /**
     * @param int $id
     * @param UpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function assignUser(int $id, UpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->permission_repository->assignUser($id, $request));
    }
}
