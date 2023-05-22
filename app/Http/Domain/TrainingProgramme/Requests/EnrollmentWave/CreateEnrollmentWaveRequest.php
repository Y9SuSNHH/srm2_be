<?php

namespace App\Http\Domain\TrainingProgramme\Requests\EnrollmentWave;

use App\Eloquent\EnrollmentWave as EloquentEnrollmentWave;
use App\Helpers\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Class CreateEnrollmentWaveRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\EnrollmentWave
 *
 * @property int $area_id
 * @property Carbon $first_day_of_school
 * @property int|null $school_year
 * @property int $group_number
 * @property Carbon $enrollment_start_date
 * @property Carbon $enrollment_end_date
 * @property Carbon|null $application_submission_deadline
 * @property Carbon|null $tuition_payment_deadline
 * @property Carbon|null $locked
 */
class CreateEnrollmentWaveRequest extends Request
{
    /**
     * @var array
     */
    protected $casts = [
        'school_id'                       => Request::CAST_INT,
        'area_id'                         => Request::CAST_INT,
        'first_day_of_school'             => Request::CAST_CARBON,
        'school_year'                     => Request::CAST_INT,
        'group_number'                    => Request::CAST_INT,
        'enrollment_start_date'           => Request::CAST_CARBON,
        'enrollment_end_date'             => Request::CAST_CARBON,
        'application_submission_deadline' => Request::CAST_CARBON,
        'tuition_payment_deadline'        => Request::CAST_CARBON,
        'locked'                          => Request::CAST_INT,
    ];

    public function prepareInput(array $input): array
    {
        $input['enrollment_start_date'] = Carbon::now()->toDateTimeString();
        $input['enrollment_end_date'] = Carbon::now()->toDateTimeString();
        return  $input;
    }

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        $id = $this->httpRequest()->id ?? null;

        return [
            'area_id' => [
                'required',
                'integer',
                Rule::exists('areas', 'id')->where('school_id', school()->getId()),
            ],
            'first_day_of_school' => [
                'required',
                'date',
                'before_or_equal:9999-12-31',
                function ($attribute, $value, $fail) use ($input, $id) {
                    $query = EloquentEnrollmentWave::query()->where('area_id', $input['area_id'])
                        ->where('first_day_of_school', $value)
                        ->where('school_id', school()->getId());
                    $count = $id ? $query->where('id', '<>', $id)->count() : $query->count();

                    if ( $count > 0 ) {
                        return $fail(__("validation.attributes.$attribute") . ' đã có trong cơ sở dữ liệu');
                    }
                }
            ],
            'group_number'                    => 'required|integer',
//            'enrollment_start_date'           => 'required|date|before_or_equal:enrollment_end_date',
//            'enrollment_end_date'             => 'required|date|before_or_equal:first_day_of_school',
            'application_submission_deadline' => 'nullable|date',
            'tuition_payment_deadline'        => 'nullable|date',
            'locked'                          => 'nullable|int',
        ];
    }

    public function attributes(): array
    {
        return [
            'area_id' => 'Trạm',
            'group_number' => 'Gộp',
            'enrollment_start_date' => 'Ngày bắt đầu tuyển sinh',
            'enrollment_end_date' => 'Ngày kết thúc tuyển sinh',
        ];
    }
}
