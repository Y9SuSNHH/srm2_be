<?php

namespace App\Http\Domain\Workflow\Providers;

use App\Http\Domain\Workflow\Repositories\WorkflowApproval\WorkflowApprovalRepository;
use App\Http\Domain\Workflow\Repositories\WorkflowApproval\WorkflowApprovalRepositoryInterface;
use App\Http\Domain\Workflow\Repositories\WorkflowFormValue\WorkflowFormValueRepositoryInterface;
use App\Http\Domain\Workflow\Repositories\WorkflowFormValue\WorkflowFormValueRepository;
use App\Http\Domain\Workflow\Repositories\Workflow\WorkflowRepositoryInterface;
use App\Http\Domain\Workflow\Repositories\Workflow\WorkflowRepository;
use App\Http\Domain\Workflow\Repositories\WorkflowValue\WorkflowValueRepositoryInterface;
use App\Http\Domain\Workflow\Repositories\WorkflowValue\WorkflowValueRepository;
use App\Http\Domain\Workflow\Repositories\WorkflowStructure\WorkflowStructureRepository;
use App\Http\Domain\Workflow\Repositories\WorkflowStructure\WorkflowStructureRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(WorkflowApprovalRepositoryInterface::class, WorkflowApprovalRepository::class);
        $this->app->singleton(WorkflowFormValueRepositoryInterface::class, WorkflowFormValueRepository::class);
        $this->app->singleton(WorkflowRepositoryInterface::class, WorkflowRepository::class);
        $this->app->singleton(WorkflowValueRepositoryInterface::class, WorkflowValueRepository::class);
        $this->app->singleton(WorkflowStructureRepositoryInterface::class, WorkflowStructureRepository::class);
    }
}
