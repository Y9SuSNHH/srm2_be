<?php

namespace App\Http\Domain\Registration\Requests\Registration;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;

/**
 * Class SearchRequest
 * @package App\Http\Domain\Registration\Requests\Registration
 *
 */
class SearchRequest extends BaseSearchRequest
{
  protected $casts = [
    'fullname'                  => Request::CAST_STRING,
    'phone_number'              => Request::CAST_STRING
  ];

  public function rules(array $input): array
  {
    return array_merge(parent::rules($input), [
      'fullname'            => [
          'nullable',
      ],
      'phone_number'        => [
          'nullable',
      ],
    ]);
  }
}
