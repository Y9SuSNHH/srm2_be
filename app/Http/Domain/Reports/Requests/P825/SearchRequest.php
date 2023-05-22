<?php

namespace App\Http\Domain\Reports\Requests\P825;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\Reports\Requests\P825
 *
 * @property $team
 */
class SearchRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'from'                  => 'nullable|date|before_or_equal:9999-12-31',
            'to'                    => 'nullable|date|before_or_equal:9999-12-31',
            'g_date'                => 'nullable|date|before_or_equal:9999-12-31',
            'semester'              => 'nullable|integer',
            'classes'               => 'nullable',
        ];
    }

    /**
     * attributes
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'from'                  => 'Từ ngày',
            'to'                    => 'Đến ngày',
            'g_date'                => 'Ngày ký G',
            'semester'              => 'Đợt học',
            'classes'               => 'Lớp',
        ];
    }
    
    /**
     * messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'integer'   => ':attribute phải là số',
            'date'      => ':attribute phải có định dạng yyyy-mm-dd'
        ];
    }
}