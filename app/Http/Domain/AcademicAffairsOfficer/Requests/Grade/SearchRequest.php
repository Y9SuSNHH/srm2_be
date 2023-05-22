<?php


namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Grade;


use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\AcademicAffairsOfficer\Requests\Grade
 *
 * @property $learning_module_id
 * @property $classroom_id
 * @property \Carbon\Carbon $start
 * @property \Carbon\Carbon $ended
 */
class SearchRequest extends BaseSearchRequest implements PaginateSearchRequest
{
    protected $casts = [
        'exam_date' => Request::CAST_CARBON,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'learning_module_id' => [
                'nullable',
                'integer',
                Rule::exists('learning_modules', 'id')->where(function ($query) {
                    /** @var Builder $query */
                    $query->orWhereNull('deleted_time')->orWhere('deleted_time', 0);
                })
            ],
            'classroom_id' => [
                'nullable',
                'integer',
                Rule::exists('classrooms', 'id')->where(function ($query) {
                    /** @var Builder $query */
                    $query->orWhereNull('deleted_time')->orWhere('deleted_time', 0);
                })
            ],
            'start' => [
                'nullable',
                'date',
                'before_or_equal:9999-12-31',
            ],
            'ended' => [
                'nullable',
                'date',
                'before_or_equal:9999-12-31',
            ],
            'fullname'     => [
                'nullable',
            ],
            'student_code' => [
                'nullable', 
            ],
        ]);
    }

    public function attributes(): array
    {
        return [
            'learning_module_id' => 'Học phần'
        ];
    }
}