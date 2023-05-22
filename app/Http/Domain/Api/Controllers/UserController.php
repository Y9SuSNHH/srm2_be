<?php

namespace App\Http\Domain\Api\Controllers;

use App\Http\Domain\Api\Repositories\User\UserRepositoryInterface;
use App\Http\Domain\Api\Requests\User\CreateRequest;
use App\Http\Domain\Api\Requests\User\UpdateRequest;
use App\Http\Domain\Common\Requests\BaseSearchRequest;

class UserController
{
    /** @var UserRepositoryInterface */
    private $user_repository;

    /**
     * UserController constructor.
     * @param UserRepositoryInterface $repository
     */
    public function __construct(UserRepositoryInterface $repository)
    {
        $this->user_repository = $repository;
    }

    /**
     * @param BaseSearchRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(BaseSearchRequest $request): \Illuminate\Http\JsonResponse
    {
        return json_response(true, $this->user_repository->getAll($request));
    }

    /**
     * @param CreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function create(CreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->throwJsonIfFailed();
        activity_history()->create($request->validated());
        $result = $this->user_repository->create($request->createAttributes());
        activity_history()->append();

        return json_response(true, $result);
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
        activity_history()->update($request->validated());
        $result = $this->user_repository->update($id, $request->updateAttributes());

        return json_response(true, $result);
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete(int $id): \Illuminate\Http\JsonResponse
    {
        $this->user_repository->delete($id);
        activity_history()->delete(compact('id'))->append();

        return json_response(true);
    }
}
