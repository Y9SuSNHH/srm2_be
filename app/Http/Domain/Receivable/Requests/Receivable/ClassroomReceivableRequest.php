<?php

namespace App\Http\Domain\Receivable\Requests\Receivable;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Class ClassroomReceivableRequest
 * @package App\Http\Request\Receivable
 *
 * @property \Carbon\Carbon $began_date
 * @property array $input
 */
class ClassroomReceivableRequest extends BaseSearchRequest
{
    protected $casts = [
        'began_date' => Request::CAST_CARBON,
        'input'      => Request::CAST_ARRAY,
    ];

    /**
     * @param array $input
     * @return \string[][]
     */
    public function rules(array $input): array
    {
        return [
            'began_date' => [
                'required',
                'date',
                'before_or_equal:9999-12-31',
            ],
            'input' => [
                'required',
                'array',
            ],
            'input.*.classroom_id' => [
                'required',
                Rule::exists('classrooms', 'id')
            ],
            'input.*.semester' => [
                'required',
                'integer',
            ],
            'input.*.purpose' => [
                'required',
                'string',
                'max:100',
            ],
            'input.*.so_tien' => [
                'required',
                'integer',
            ],
            'input.*.classroom_receivable_id' => [
                'nullable',
                'integer',
                Rule::exists('classroom_receivables', 'id'),
            ],
        ];
    }
}
