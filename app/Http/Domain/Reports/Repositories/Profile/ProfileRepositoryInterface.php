<?php


namespace App\Http\Domain\Reports\Repositories\Profile;


interface ProfileRepositoryInterface
{
  public function insert(array $data);
  public function findExistedProfiles($identifications);
}