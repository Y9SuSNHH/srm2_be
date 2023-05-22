<?php

namespace App\Http\Domain\Student\Controllers;

use App\Http\Domain\Student\Repositories\StudentRevisionHistory\StudentRevisionHistoryRepository;
use App\Http\Domain\Student\Repositories\StudentRevisionHistory\StudentRevisionHistoryRepositoryInterface;
use App\Http\Domain\Student\Services\StudentHistoryService;

/**
 * Class StudentStatusController
 * @package App\Http\Domain\Student\Controllers
 */
class StudentStatusController
{
    /** @var StudentRevisionHistoryRepository */
    private $student_revision_history_repository;

    public function __construct(StudentRevisionHistoryRepositoryInterface $repository)
    {
        $this->student_revision_history_repository = $repository;
    }

    /**
     * @param int $student_id
     * @param StudentHistoryService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(int $student_id, StudentHistoryService $service): \Illuminate\Http\JsonResponse
    {
        return json_response(true, $service->getAll($student_id));
    }
}
