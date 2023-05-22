<?php

namespace App\Http\Domain\Finance\Requests\Finance;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Carbon\Carbon;

/**
 * Class SearchRequest
 * @package App\Http\Request\Receivable
 *
 * @property \Carbon\Carbon $began_date
 * @property $semester
 * @property $major
 * @property $staff
 * @property $class
 */
class FilterRequest extends BaseSearchRequest
{
    protected $casts = [
        'staff'         => Request::CAST_INT,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'staff'  => [],
        ]);
    }
}
