<?php

namespace App\Http\Domain\Student\Repositories\LearningModule;

use Illuminate\Database\Eloquent\Collection;

interface LearningModuleRepositoryInterface
{
    /**
     * @param array $code
     * @param array $get
     * @return Collection|array
     */
    public function getByCodes(array $code, array $get = ['*']): Collection|array;
}