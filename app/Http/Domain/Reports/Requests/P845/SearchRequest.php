<?php

namespace App\Http\Domain\Reports\Requests\P845;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\Reports\Requests\P845
 *
 * @property $team
 */
class SearchRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'staff'                 => 'nullable|integer',
            'major'                 => 'nullable|integer',
            'collect_semester'      => 'nullable|integer',
            'chosen_report'         => 'nullable|string',
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
            'staff'                 => 'Quản lý học tập',
            'major'                 => 'Ngành',
            'collect_semester'      => 'Kỳ thu',
            'chosen_report'         => 'Tên báo cáo',
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
            'string'    => ':attribute phải là chữ',
        ];
    }
}