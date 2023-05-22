<?php

namespace App\Http\Domain\Api\Controllers;

use App\Http\Domain\Api\Repositories\Role\RoleRepositoryInterface;
use App\Http\Domain\Api\Requests\Role\CreateRequest;
use App\Http\Domain\Api\Requests\Role\UpdateRequest;
use App\Http\Domain\Common\Requests\BaseSearchRequest;

class RoleController
{
    /** @var RoleRepositoryInterface */
    private $role_repository;

    /**
     * RoleController constructor.
     * @param RoleRepositoryInterface $repository
     */
    public function __construct(RoleRepositoryInterface $repository)
    {
        $this->role_repository = $repository;
    }

    /**
     * @param BaseSearchRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(BaseSearchRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->role_repository->getAll($request));
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        return json_response(true, $this->role_repository->getById($id));
    }

    /**
     * @param CreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function create(CreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->role_repository->create($request));
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
        return json_response(true, $this->role_repository->update($id, $request));
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(int $id): \Illuminate\Http\JsonResponse
    {
        return json_response(true, $this->role_repository->delete($id));
    }

    /**
     * @param int $id
     * @param UpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function assign(int $id, UpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->role_repository->assignUser($id, $request));
    }

    /**
     * @param int $id
     * @param UpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function assignPermission(int $id, UpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->role_repository->assignPermission($id, $request));
    }
}
