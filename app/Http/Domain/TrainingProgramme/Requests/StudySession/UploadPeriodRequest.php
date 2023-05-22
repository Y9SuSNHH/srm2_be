<?php

namespace App\Http\Domain\TrainingProgramme\Requests\StudySession;

use App\Helpers\Request;

/**
 * Class UploadPeriodRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\StudySession
 *
 * @property \Illuminate\Http\UploadedFile $file
 * @property $passed
 */
class UploadPeriodRequest extends Request
{
    /**
     * @param array $input
     * @return \string[][]
     */
    public function rules(array $input): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,txt'
            ],
            'passed' => [
                'required',
                'string',
            ],
        ];
    }
}