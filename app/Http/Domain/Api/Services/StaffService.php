<?php

namespace App\Http\Domain\Api\Services;
use App\Http\Domain\Api\Repositories\Staff\StaffRepositoryInterface;
class StaffService
{
  private $staff_repository;

  /**
   * __construct
   *
   * @param  mixed $staff_repository
   * @return void
   */
  public function __construct(StaffRepositoryInterface $staff_repository)
  {
    $this->staff_repository = $staff_repository;
  }
    
  /**
   * getStaffInfo
   *
   * @return void
   */
  public function getStaffInfo()
  {
    $staff = $this->staff_repository->getStaff();
    return $staff;
  }

  public function findExistedStaffs(array $usernames)
    {
        return  $this->staff_repository->findStaff($usernames);
    }
}