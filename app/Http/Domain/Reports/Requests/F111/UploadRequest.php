<?php

namespace App\Http\Domain\Reports\Requests\F111;

use App\Helpers\Request;

/**
 * Class UploadRequest
 * @package App\Http\Domain\Reports\Requests\F111
 *
 * @property \Illuminate\Http\UploadedFile $file
 * @property $passed
 */
class UploadRequest extends Request
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
