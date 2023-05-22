<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\StudySession;

use App\Http\Domain\Common\Repositories\BaseRepositoryInterface;

interface PeriodRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return array
     */
    public function getExistSemester(): array;

    /**
     * @param array $attribute
     * @return bool
     */
    public function insert(array $attribute): bool;
}
