<?php

namespace App\Http\Domain\Reports\Requests\G820;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\Reports\Requests\G820
 *
 * @property $team
 */
class SearchRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'g_date'                => 'nullable|date|before_or_equal:9999-12-31',
            'semester'              => 'nullable|integer',
            'major'                 => 'nullable|string',
            'staff'                 => 'nullable|integer',
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
            'g_date'                => 'Ngày ký G',
            'semester'              => 'Đợt học',
            'major'                 => 'Ngành đào tạo',
            'staff'                 => 'QLHT',
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
            'string'    => ':attribute phải là dạng chuỗi',
            'integer'   => ':attribute phải là số',
            'date'      => ':attribute phải có định dạng yyyy-mm-dd'
        ];
    }
}