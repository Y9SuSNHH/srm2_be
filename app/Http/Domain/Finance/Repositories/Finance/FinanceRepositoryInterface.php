<?php

namespace App\Http\Domain\Finance\Repositories\Finance;

use App\Http\Domain\Finance\Requests\Finance\EditRequest;
use App\Http\Domain\Finance\Requests\Finance\SearchRequest;
use App\Http\Domain\Finance\Requests\Finance\FilterRequest;
use App\Http\Domain\Finance\Requests\Finance\StudentClassRequest;
use App\Http\Domain\Finance\Requests\Finance\TuitionRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

interface FinanceRepositoryInterface
{
    public function getByClass(SearchRequest $request): LengthAwarePaginator;

    public function getFilter(FilterRequest $request);

    public function getByStudent(SearchRequest $request): LengthAwarePaginator;

    public function tuition(TuitionRequest $request): LengthAwarePaginator;

    public function studentClass(StudentClassRequest $request);

    public function semesterClass();

    public function receiveSemester(SearchRequest $request);
    
    public function filterStudent(int $purpose);

    public function delete(int $id);

    public function update(int $id, EditRequest $request);
}
