<?php

namespace App\Http\Domain\Student\Requests\CareHistory;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;

/**
 * Class SearchRequest
 * @package App\Http\Domain\Student\Requests\CareHistory
 *
 * @property string|null $student_id
 * @property string|null $care_history_status
 */
class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
      'student_id'                    => Request::CAST_INT,
      'care_history_status'           => Request::CAST_INT,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'student_id' => [
                'nullable',
            ],
            'care_history_status' => [
                'nullable',
            ],
        ]);
    }
}
