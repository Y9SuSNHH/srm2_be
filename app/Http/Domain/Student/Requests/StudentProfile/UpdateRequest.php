<?php

namespace App\Http\Domain\Student\Requests\StudentProfile;

use App\Eloquent\Area;
use App\Helpers\Request;
use App\Http\Enum\ProfileStatus;
use Illuminate\Validation\Rule;

class UpdateRequest extends Request
{

    public function rules(array $input): array
    {
        return [
            'profile_status'                             => [
                'integer',
                Rule::in(ProfileStatus::toArray()),
            ],
            'documents'                                  => [
                'nullable',
                'array',
            ],
            'documents.date_delivery_document_admission' => [
                'nullable',
                'date',
            ],
            'documents.delivery_date_tvu'                => [
                'nullable',
                'date',
            ],
            'documents.profile_receive_area'             => [
                'nullable',
                'string',
            ],
            'documents.profile_status_tkts'              => [
                'nullable',
                'string',
            ],
            'documents.receive_date'                     => [
                'nullable',
                'date',
            ],
            'documents.report_error'                     => [
                'nullable',
                'string',
            ],
            'documents.student_card_received_date'       => [
                'nullable',
                'date',
            ],
        ];
    }
}