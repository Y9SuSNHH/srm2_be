<?php

namespace App\Http\Domain\Receivable\Repositories\Receivable;

use App\Http\Domain\Receivable\Requests\Receivable\SearchRequest;
use App\Http\Domain\Receivable\Requests\Receivable\ClassroomReceivableRequest;
use App\Http\Domain\Receivable\Requests\Receivable\CreateStudentReceivableRequest;
use App\Http\Domain\Receivable\Requests\Receivable\EditStudentReceivableRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

interface ReceivableRepositoryInterface
{
    // public function fetchBeganDate();
    public function fetchPeriod();

    // public function fetchSemester();

    // public function fetchClasses(SearchRequest $request);

    public function getAllQlht();

    public function getAllClassroom();

    public function getAllMajor();

    public function getAll(SearchRequest $request);

    public function storeClassroomReceivable(ClassroomReceivableRequest $request);

    public function storeStudentReceivable(CreateStudentReceivableRequest $request);

    public function updateStudentReceivable(EditStudentReceivableRequest $request);
     /**
     * @param int $id
     * @return array
     */
    public function findClassroomReceivable(int $id);

}
