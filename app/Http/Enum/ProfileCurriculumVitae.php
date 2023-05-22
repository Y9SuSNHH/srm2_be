<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class ProfileCurriculumVitae extends Enum
{
    public const JOB                  = "job"; // Công việc hiện nay
    public const WARD                 = "ward";
    public const VILLAGE              = "village";
    public const DISTRICT             = "district";
    public const CITY                 = "city";
    public const MAJORED_IN           = "majored_in"; // Ngành đã học
    public const CERTIFICATE          = "certificate"; // Bằng tốt nghiệp
    public const DEGREE_PLACE         = "degree_place"; // Nơi cấp bằng
    public const PLACE_OF_ISSUE       = "place_of_issue";
    public const WORKING_AGENCY       = "working_agency"; //Nơi công tác
    public const GRADUATION_YEAR      = "graduation_year"; //Năm tốt nghiệp
    public const HIGH_SCHOOL_NAME     = "high_school_name"; // Trường học
    public const HIGH_SCHOOL_DISTRICT = "high_school_district"; // Quận/huyện
    public const HIGH_SCHOOL_CITY     = "high_school_city"; // Tỉnh/TP
    public const DEPUTY_1             = "deputy_1"; // Người đại diện 1
    public const DEPUTY_RELATION_1    = "deputy_relation_1"; // Quan hệ
    public const DEPUTY_JOB_1         = "deputy_job_1"; // Nghề nghiệp
    public const DEPUTY_PHONE_1       = "deputy_phone_1"; // Điện thoại
    public const DEPUTY_ADDRESS_1     = "deputy_address_1"; // Địa chỉ
    public const DEPUTY_2             = "deputy_2"; //Người đại diện 2
    public const DEPUTY_RELATION_2    = "deputy_relation_2"; //Quan hệ
    public const DEPUTY_JOB_2         = "deputy_job_2"; //Nghề nghiệp
    public const DEPUTY_PHONE_2       = "deputy_phone_2"; //Điện thoại
    public const DEPUTY_ADDRESS_2     = "deputy_address_2"; //Địa chỉ
}