<?php

namespace App\Http\Domain\TrainingProgramme\Requests\LearningModule;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Validation\Rule;

/**
 * Class SearchRequest
 * @package App\Http\Domain\TrainingProgramme\Requests\LearningModule
 *
 * @property int|null $subject_id
 */
class SearchRequest extends BaseSearchRequest
{
    protected $casts = [
        'keyword' => Request::CAST_STRING,
    ];

    public function rules(array $input): array
    {
        return array_merge(parent::rules($input), [
            'keyword' => [
                'nullable',
            ],
        ]);
    }
}
