<?php

namespace App\Http\Domain\TrainingProgramme\Requests\Curriculum;

use App\Helpers\Request;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\Curriculum
 *
 * @property $team
 */
class SearchRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'major' => 'nullable|string',
        ];
    }
}