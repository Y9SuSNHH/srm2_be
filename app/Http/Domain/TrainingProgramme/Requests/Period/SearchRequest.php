<?php

namespace App\Http\Domain\TrainingProgramme\Requests\Period;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\Period
 *
 */
class SearchRequest extends BaseSearchRequest
{
  protected $casts = [
    'semester'                  => Request::CAST_INT,
    'classroom_id'              => Request::CAST_STRING,
    'learn_began_date'          => Request::CAST_CARBON,
    'collect_ended_date'        => Request::CAST_CARBON,
    'decision_date'             => Request::CAST_CARBON,
    'is_final'                  => Request::CAST_BOOL,
  ];

  public function rules(array $input): array
  {
    return array_merge(parent::rules($input), [
      'semester' => [
        'nullable',
        Rule::exists('periods', 'semester'),
      ],
      'classroom_id' => [
        'nullable',
        Rule::exists('periods', 'classroom_id'),
      ],
      'learn_began_date' => [
        'nullable',
        Rule::exists('periods', 'learn_began_date'),
      ],
      'collect_began_date' => [
        'nullable',
        Rule::exists('periods', 'collect_ended_date'),
      ],
      'decision_date' => [
        'nullable',
        Rule::exists('periods', 'decision_date'),
      ],
      'is_final' => [
        'nullable',
        Rule::exists('periods', 'is_final'),
      ],
    ]);
  }
}
