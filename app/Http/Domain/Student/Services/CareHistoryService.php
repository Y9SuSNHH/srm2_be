<?php

namespace App\Http\Domain\Student\Services;

use App\Http\Domain\Student\Repositories\CareHistory\CareHistoryRepositoryInterface;
use App\Http\Domain\Student\Requests\CareHistory\SearchRequest;
use App\Http\Domain\Api\Services\StaffService;

class CareHistoryService
{
  /**
   * care_history_repository
   *
   * @var mixed
   */
  private $care_history_repository;

  /**
   * __construct
   *
   * @param  mixed $care_history_repository
   * @return void
   */
  public function __construct(CareHistoryRepositoryInterface $care_history_repository)
  {
    $this->care_history_repository = $care_history_repository;
  }

  /**
   * store
   *
   * @return void
   */
  public function store(SearchRequest $request)
  {
    $care_history = $this->care_history_repository->careHistoryRepositoryQuery($request)->get();
    $staff = app()->service(StaffService::class)->getStaffInfo();

    $result = $care_history->transform(function ($care_history) use ($staff) {
      return [
        'id' => $care_history->id,
        'student_id' => $care_history->student_id,
        'content' => $care_history->content,
        'status' => $care_history->status,
        'created_at' => $care_history->created_at,
        'created_by' => $care_history->created_by,
        'updated_at' => $care_history->updated_at,
        'updated_by' => $care_history->updated_by,
        'fullname_created_by' => $care_history->created_by ? ($staff->where('user_id', $care_history->created_by)->first() ? $staff->where('user_id', $care_history->created_by)->first()->fullname : null) : null,
        'fullname_updated_by' => $care_history->updated_by ? ($staff->where('user_id', $care_history->updated_by)->first() ? $staff->where('user_id', $care_history->updated_by)->first()->fullname : null) : null,
      ];
    });
    return $result;
  }
}
