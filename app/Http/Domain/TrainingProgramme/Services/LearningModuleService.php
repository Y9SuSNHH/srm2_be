<?php

namespace App\Http\Domain\TrainingProgramme\Services;

use App\Http\Domain\TrainingProgramme\Repositories\LearningModule\LearningModuleRepository;
use App\Http\Domain\TrainingProgramme\Repositories\LearningModule\LearningModuleRepositoryInterface;

class LearningModuleService
{  
  /**
   * learning_module_repository
   *
   * @var LearningModuleRepository
   */
  private $learning_module_repository;
  
  /**
   * __construct
   *
   * @param  mixed $learning_module_repository
   * @return void
   */
  public function __construct(LearningModuleRepositoryInterface $learning_module_repository)
  {
    $this->learning_module_repository = $learning_module_repository;
  }
  
  /**
   * getList
   *
   * @param  mixed $request
   * @return void
   */
  public function getLearningModules()
  {
    return  $this->learning_module_repository->getListItems();
  }

  public function getOptions()
  {
    return $this->learning_module_repository->options();
  }

    /**
     * @param int $id
     * @return array
     */
  public function getById(int $id): array
  {
      return $this->learning_module_repository->getById($id);
  }
}
