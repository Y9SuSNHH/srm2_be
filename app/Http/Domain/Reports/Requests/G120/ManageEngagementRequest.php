<?php

namespace App\Http\Domain\Reports\Requests\G120;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class ManageEngagementRequest
 * @package App\Http\Domain\Reports\Requests\G120
 *
 * @property $team
 */
class ManageEngagementRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'row.*.id'                              => 'required|integer',
            'row.*.is_join_first_day_of_school'     => 'required|integer',
            'row.*.is_join_first_week'              => 'required|integer',
            'row.*.is_join_fourth_week'             => 'required|integer',
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
            'row.*.row.*.id'                        => 'ID sinh viên',
            'row.*.is_join_first_day_of_school'     => 'Tình trạng tham gia khai giảng',
            'row.*.is_join_first_week'              => 'Tình trạng tham gia học tập tuần 1',
            'row.*.is_join_fourth_week'             => 'Tình trạng tham gia học tập tuần 4',
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
            'required'   => ':attribute không được để trống',
            'integer'   => ':attribute phải là số',
        ];
    }
}