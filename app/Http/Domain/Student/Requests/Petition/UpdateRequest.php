<?php

namespace App\Http\Domain\Student\Requests\Petition;

use App\Eloquent\Area;
use App\Eloquent\Classroom;
use App\Eloquent\Petition;
use App\Eloquent\PetitionFlow;
use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use App\Http\Domain\Student\Services\PetitionService;
use App\Http\Enum\PetitionContent;
use App\Http\Enum\PetitionContentType;
use App\Http\Enum\PetitionFlowStatus;
use App\Http\Enum\PetitionStatus;
use App\Http\Enum\RoleAuthority;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateRequest extends Request
{
    protected $casts = [
        'new_content'       => Request::CAST_ARRAY,
        'effective_date'    => Request::CAST_CARBON,
        'date_of_amendment' => Request::CAST_CARBON,
        'note'              => Request::CAST_STRING,
        'no'                => Request::CAST_INT,
        'is_update_student' => Request::CAST_BOOL,
    ];

    public function rules(array $input): array
    {
        $rules = [
            'effective_date'        => [
                'nullable',
                'date',
            ],
            'date_of_amendment'     => [
                'nullable',
                'date',
            ],
            'note'                  => [
                'nullable',
                'string',
            ],
            'no'                    => [
                'nullable',
                'integer',
            ],
            'is_update_student'     => [
                'required',
                'boolean'
            ],
            'new_content'           => [
                'nullable',
                'array',
            ],
            'new_content.classroom' => [
                'numeric',
                Rule::exists(Classroom::class, 'id'),
            ],
            'new_content.area'      => [
                'numeric',
                Rule::exists(Area::class, 'id'),
                Rule::exists(Classroom::class, 'area_id'),
            ],
        ];
        if (RoleAuthority::ACADEMIC_AFFAIRS_OFFICER()->check()) {
            $last            = PetitionFlow::query()->where('petition_id', $this->httpRequest()->id)->where('role_authority', RoleAuthority::ACADEMIC_AFFAIRS_OFFICER)->oldest()->first();
            $rules['status'] = [
                'required',
                'integer',
                Rule::in(array_merge(PetitionFlowStatus::academicAffair(), PetitionFlowStatus::thirdParty())),
            ];
            if (!empty($last)) {
                $rules['status'][] = 'min:' . ($last->status + 1);
            }
        }
        return $rules;
    }
}