<?php

namespace App\Http\Domain\Student\Requests\IgnoreLearningModule;

use App\Helpers\Request;

/**
 * @property mixed $passed
 * @property mixed $file
 */
class ImportRequest extends Request
{
    /**
     * @param array $input
     * @return string[][]
     */
    public function rules(array $input): array
    {
        return [
            'file'   => [
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