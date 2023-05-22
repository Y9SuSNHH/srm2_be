<?php

namespace App\Http\Domain\Finance\Requests\Finance;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Carbon\Carbon;

class EditRequest extends BaseSearchRequest
{
    protected $casts = [
        'note'   => Request::CAST_STRING,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'note'   => [],
        ]);
    }
}
