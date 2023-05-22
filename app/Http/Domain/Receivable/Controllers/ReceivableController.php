<?php

namespace App\Http\Domain\Receivable\Controllers;


use App\Http\Domain\Receivable\Repositories\Receivable\ReceivableRepositoryInterface;
use App\Http\Domain\Receivable\Requests\Receivable\SearchRequest;
use App\Http\Domain\Receivable\Requests\Receivable\ClassroomReceivableRequest;
use App\Http\Domain\Receivable\Requests\Receivable\CreateStudentReceivableRequest;
use App\Http\Domain\Receivable\Requests\Receivable\EditStudentReceivableRequest;
use App\Http\Domain\Receivable\Requests\Receivable\StudentReceivableRequest;
use App\Http\Domain\Receivable\Services\ReceivableService;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;

/**
 * Class StudentController
 * @package App\Http\Domain\Student\Controllers
 */
class ReceivableController extends Controller
{
    /**
     * @param SearchRequest $request
     * @param ReceivableRepositoryInterface $receivable_repository
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(SearchRequest $request, ReceivableRepositoryInterface $receivable_repository): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $receivable_repository->getAll($request), []);
    }

    public function show(int $id, ReceivableService $receivable_service): JsonResponse
    {
        return json_response(true, $receivable_service->findClassroomReceivable($id));
    }

    public function fetchPeriod(ReceivableRepositoryInterface $receivable_repository) : JsonResponse
    {
        return json_response(true, $receivable_repository->fetchPeriod(), []);
    }

    // public function fetchClasses(ReceivableRepositoryInterface $receivable_repository, SearchRequest $request, ReceivableService $receivable_service): JsonResponse
    // {
    //     $request->throwJsonIfFailed();
    //     // cách gọi thẳng service 
    //     // controller -> repo lấy hết dữ liệu truyền service
    //     return json_response(true, $receivable_service->getClasses($request, $receivable_repository), []);
    // }

    public function getAllQlht(ReceivableRepositoryInterface $receivable_repository): JsonResponse
    {
        return json_response(true, $receivable_repository->getAllQlht(), []);
    }

    public function getAllMajor(ReceivableRepositoryInterface $receivable_repository): JsonResponse
    {
        return json_response(true, $receivable_repository->getAllMajor(), []);
    }

    public function getAllClassroom(ReceivableRepositoryInterface $receivable_repository): JsonResponse
    {
        return json_response(true, $receivable_repository->getAllClassroom(), []);
    }

    public function storeClassroomReceivable(ReceivableRepositoryInterface $receivable_repository, ClassroomReceivableRequest $request): JsonResponse
    {
        return json_response(true, $receivable_repository->storeClassroomReceivable($request), []);
    }

    public function storeStudentReceivable(CreateStudentReceivableRequest $request, ReceivableRepositoryInterface $receivable_repository): JsonResponse
    {
        $result = $receivable_repository->storeStudentReceivable($request);
        
        return json_response($result, compact('result'));
    }

    public function updateStudentReceivable(EditStudentReceivableRequest $request, ReceivableRepositoryInterface $receivable_repository): JsonResponse
    {
        $result = $receivable_repository->updateStudentReceivable($request);
        
        return json_response($result, compact('result'));
    }



}