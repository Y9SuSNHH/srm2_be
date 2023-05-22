<?php

namespace App\Http\Domain\Student\Models\Student;

use App\Helpers\Json;

class ProfileCurriculumVitae extends Json
{
    public $job; // Công việc hiện nay
    public $ward;
    public $village;
    public $district;
    public $city;
    public $majored_in; // Ngành đã học
    public $certificate; // Bằng tốt nghiệp
    public $degree_place; // Nơi cấp bằng
    public $place_of_issue;
    public $working_agency; //Nơi công tác
    public $graduation_year; //Năm tốt nghiệp
    public $high_school_name; // Trường học
    public $high_school_district; // Quận/huyện
    public $high_school_city; // Tỉnh/TP
    public $deputy_1; // Người đại diện 1
    public $deputy_relation_1; // Quan hệ
    public $deputy_job_1; // Nghề nghiệp
    public $deputy_phone_1; // Điện thoại
    public $deputy_address_1; // Địa chỉ
    public $deputy_2; //Người đại diện 2
    public $deputy_relation_2; //Quan hệ
    public $deputy_job_2; //Nghề nghiệp
    public $deputy_phone_2; //Điện thoại
    public $deputy_address_2; //Địa chỉ
}
