<?php

namespace App\Http\Domain\Student\Requests\StudentProfile;

use App\Helpers\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class ImportStudentRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'file'                  => [
                'nullable',
                'file',
            ],
        ];
    }
}