<?php

namespace App\Http\Domain\Api\Controllers;

use App\Http\Domain\Api\Repositories\ObjectClassification\ObjectTypeRepositoryInterface;
use App\Http\Domain\Api\Requests\ObjectType\CreateObjectTypeRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;

/**
 * Class ObjectTypeController
 * @package App\Http\Domain\Api\Controllers
 */
class ObjectTypeController extends Controller
{
    /**
     * @param ObjectTypeRepositoryInterface $object_type_repository
     * @return JsonResponse
     */
    public function index(ObjectTypeRepositoryInterface $object_type_repository): JsonResponse
    {
        return json_response(true, $object_type_repository->getAll());
    }

    /**
     * @param ObjectTypeRepositoryInterface $object_type_repository
     * @param int $id
     * @return JsonResponse
     */
    public function show(ObjectTypeRepositoryInterface $object_type_repository, int $id): JsonResponse
    {
        return json_response(true, $object_type_repository->getById($id));
    }

    /**
     * @param ObjectTypeRepositoryInterface $object_type_repository
     * @param CreateObjectTypeRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(ObjectTypeRepositoryInterface $object_type_repository, CreateObjectTypeRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $object_type_repository->create($request));
    }

    /**
     * @param ObjectTypeRepositoryInterface $object_type_repository
     * @param CreateObjectTypeRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(ObjectTypeRepositoryInterface $object_type_repository, CreateObjectTypeRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $object_type_repository->update($id, $request));
    }

    /**
     * @param ObjectTypeRepositoryInterface $object_type_repository
     * @param int $id
     * @return JsonResponse
     */
    public function delete(ObjectTypeRepositoryInterface $object_type_repository, int $id): JsonResponse
    {
        return json_response(true, $object_type_repository->delete($id));
    }
}