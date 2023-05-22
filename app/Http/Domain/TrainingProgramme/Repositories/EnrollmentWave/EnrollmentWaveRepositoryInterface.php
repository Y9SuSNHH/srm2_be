<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\EnrollmentWave;

use App\Http\Domain\Common\Repositories\BaseRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\EnrollmentWave\SearchRequest;
use Illuminate\Database\Eloquent\Collection;

interface EnrollmentWaveRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param SearchRequest $request
     * @param string[] $columns
     * @return Collection
     */
    public function options(SearchRequest $request, $columns = ['*']): Collection;
}