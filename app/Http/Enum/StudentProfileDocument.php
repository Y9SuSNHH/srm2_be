<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class StudentProfileDocument extends Enum
{
    public const REGISTRATION_FORM             = 'registration_form'; // Phiếu đăng ký theo mẫu
    public const APPLICATION_FORM              = 'application_form'; // Phiếu dự tuyển
    public const GRADUATE_DEGREE               = 'graduate_degree';// Bằng tốt nghiệp
    public const TRANSCRIPT                    = 'transcript'; // Bảng điểm
    public const CARD_IMAGE                    = 'card_image'; // Ảnh 3x4
    public const PROFILE_RECEIVE_AREA          = 'profile_receive_area'; // Khu vực tiếp nhận hồ sơ
    public const UNIVERSITY                    = 'university'; // Trường
    public const GOP_BAN_DAU                   = 'gop_ban_dau'; // Gộp ban đầu
    public const GOP_CHUYEN_DEN                = 'gop_chuyen_den'; // Gộp chuyển đến
    public const GOP_DANG_KI                   = 'gop_dang_ki'; // Gộp đăng kí
    public const GOP_KHAI_GIANG                = 'gop_khai_giang'; // Gộp khai giảng
    public const TUAN                          = 'tuan'; // Tuần
    public const RECEIVE_DATE                  = 'receive_date'; // Ngày nhận hồ sơ từ TVTS
    public const DELIVERY_DATE_TVU             = 'delivery_date_tvu'; // Ngày bàn giao TVU Cứng
    public const DELIVERY_DATE_TVU_SCAN        = 'delivery_date_tvu_scan'; // Ngày bàn giao TVU scan
    public const REPORT_ERROR                  = 'report_error'; // Phản hồi hồ sơ lỗi giáo vụ
    public const DECISION_NO                   = 'decision_no'; // Số Quyết định
    public const DECISION_DATE                 = 'decision_date'; // Ngày ký QĐTT
    public const DECISION_RETURN_DATE          = 'decision_return_date'; // Ngày GV trả QĐ cho TVTS
    public const STUDENT_CARD_RECEIVED_DATE    = 'student_card_received_date'; // Ngày TVU trả thẻ SV
    public const DELIVERY_STUDENT_PROFILE_DATE = 'delivery_student_profile_date'; // Ngày giáo vụ BGHS trường
    public const SUBJECT                       = 'subject'; // Tổ hợp xét tuyển
    public const SUBJECT_CODE                  = 'subject_code'; // Mã hợp xét tuyển
    public const GRADE_1                       = 'grade_1'; // Điểm tổ hợp 1
    public const GRADE_2                       = 'grade_2'; // Điểm tổ hợp 2
    public const GRADE_3                       = 'grade_3'; // Điểm tổ hợp 3
    public const GRADE_SUBJECT                 = 'grade_subject'; // Điểm xét tuyển
    public const GRADE_AVG_SUBJECT             = 'grade_avg_subject'; // Điểm TB TC,CĐ,ĐH
    public const RANK_SUBJECT                  = 'rank_subject'; // Xếp loại tốt nghiệp
    public const PROFILE_STATUS_TKTS           = 'profile_status_tkts'; // Trạng thái HS TKTS
}