<?php

namespace App\Http\Domain\Student\Requests\Profile;

use App\Eloquent\Classroom;
use App\Eloquent\Staff;
use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends Request
{
    protected $casts = [
        'profile_code'         => Request::CAST_STRING,
        'gop_dang_ki'          => Request::CAST_STRING,
        'gop_khai_giang'       => Request::CAST_STRING,
        'tuan'                 => Request::CAST_STRING,
        'classroom_id'         => Request::CAST_INT,
        'first_day_of_school'  => Request::CAST_CARBON,
        'staff_id'             => Request::CAST_INT,
        'firstname'            => Request::CAST_STRING,
        'lastname'             => Request::CAST_STRING,
        'gender'               => Request::CAST_BOOL,
        'birthday'             => Request::CAST_CARBON,
        'borned_place'         => Request::CAST_STRING,
        'phone_number'         => Request::CAST_INT,
        'email'                => Request::CAST_STRING,
        'nation'               => Request::CAST_STRING,
        'religion'             => Request::CAST_STRING,
        'identification'       => Request::CAST_INT,
        'grant_date'           => Request::CAST_CARBON,
        'grant_place'          => Request::CAST_STRING,
        'main_residence'       => Request::CAST_STRING,
        'address'              => Request::CAST_STRING,
        'majored_in'           => Request::CAST_STRING,
        'certificate'          => Request::CAST_STRING,
        'graduation_year'      => Request::CAST_INT,
        'degree_place'         => Request::CAST_STRING,
        'high_school_name'     => Request::CAST_STRING,
        'high_school_district' => Request::CAST_STRING,
        'high_school_city'     => Request::CAST_STRING,
        'job'                  => Request::CAST_STRING,
        'working_agency'       => Request::CAST_STRING,
        'deputy_1'             => Request::CAST_STRING,
        'deputy_relation_1'    => Request::CAST_STRING,
        'deputy_job_1'         => Request::CAST_STRING,
        'deputy_phone_1'       => Request::CAST_INT,
        'deputy_address_1'     => Request::CAST_STRING,
        'deputy_2'             => Request::CAST_STRING,
        'deputy_relation_2'    => Request::CAST_STRING,
        'deputy_job_2'         => Request::CAST_STRING,
        'deputy_phone_2'       => Request::CAST_INT,
        'deputy_address_2'     => Request::CAST_STRING,
        'note'                 => Request::CAST_STRING,
    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'profile_code'         => [
                'nullable',
                'string'
            ],
            'documents'            => [
                'nullable',
                'array:gop_dang_ki,gop_khai_giang,tuan,subject,subject_code,grade_1,grade_2,grade_3,grade_subject,grade_avg_subject,rank_subject',
            ],
            'classroom_id'         => [
                'nullable',
                'numeric',
                Rule::exists(Classroom::class, 'id'),
            ],
            'staff_id'             => [
                'nullable',
                'numeric',
                Rule::exists(Staff::class, 'id'),
            ],
            'first_day_of_school'  => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'firstname'            => [
                'required',
                'string',
            ],
            'lastname'             => [
                'required',
                'string',
            ],
            'gender'               => [
                'required',
                'boolean',
            ],
            'birthday'             => [
                'required',
                'date_format:Y-m-d',
                'before:today',
            ],
            'borned_place'         => [
                'required',
                'string',
            ],
            'phone_number'         => [
                'nullable',
                'numeric',
                'min:10',
            ],
            'email'                => [
                'nullable',
                'email',
            ],
            'nation'               => [
                'nullable',
                'string',
            ],
            'religion'             => [
                'nullable',
                'string',
            ],
            'identification'       => [
                'nullable',
                'numeric',
                'min:9',
            ],
            'grant_date'           => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'grant_place'          => [
                'nullable',
                'string',
            ],
            'main_residence'       => [
                'nullable',
                'string',
            ],
            'address'              => [
                'nullable',
                'string',
            ],
            'majored_in'           => [
                'nullable',
                'string',
            ],
            'certificate'          => [
                'nullable',
                'string',
            ],
            'graduation_year'      => [
                'nullable',
                'date_format:Y',
            ],
            'degree_place'         => [
                'nullable',
                'string',
            ],
            'high_school_name'     => [
                'nullable',
                'string',
            ],
            'high_school_district' => [
                'nullable',
                'string',
            ],
            'high_school_city'     => [
                'nullable',
                'string',
            ],
            'job'                  => [
                'nullable',
                'string',
            ],
            'working_agency'       => [
                'nullable',
                'string',
            ],
            'deputy_1'             => [
                'nullable',
                'string',
            ],
            'deputy_relation_1'    => [
                'nullable',
                'string',
            ],
            'deputy_job_1'         => [
                'nullable',
                'string',
            ],
            'deputy_phone_1'       => [
                'nullable',
                'numeric',
                'min:10',
            ],
            'deputy_address_1'     => [
                'nullable',
                'string',
            ],
            'deputy_2'             => [
                'nullable',
                'string',
            ],
            'deputy_relation_2'    => [
                'nullable',
                'string',
            ],
            'deputy_job_2'         => [
                'nullable',
                'string',
            ],
            'deputy_phone_2'       => [
                'nullable',
                'numeric',
                'min:10',
            ],
            'deputy_address_2'     => [
                'nullable',
                'string',
            ],
            'note'                 => [
                'nullable',
                'string',
            ],
        ];
    }
}
