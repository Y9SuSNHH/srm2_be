<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Grade;

use App\Helpers\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * Class ImportRequest
 * @package App\Http\Domain\AcademicAffairsOfficer\Requests\Grade
 *
 * @property $step_passed
 * @property UploadedFile $file
 * @property int $learning_module_id
 * @property \Carbon\Carbon $exam_date
 */
class ImportRequest extends Request
{
    protected $casts = [
        'exam_date' => Request::CAST_CARBON,
    ];

    public function rules(array $input): array
    {
        return [
            'file' => [
                'nullable',
                'file',
            ],
            'learning_module_id' => [
                'required',
                Rule::exists('learning_modules', 'id')
            ],
            'exam_date' => [
                'required',
                'date',
                'before_or_equal:9999-12-31',
                // Rule::exists('study_plans', 'day_of_the_test')->where('learning_module_id', $this->httpRequest()->get('learning_module_id')),
            ],
        ];
    }
}
