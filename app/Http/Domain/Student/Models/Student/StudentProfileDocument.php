<?php

namespace App\Http\Domain\Student\Models\Student;

use App\Helpers\Json;

class StudentProfileDocument extends Json
{
    public $registration_form; // Phiếu đăng ký theo mẫu
    public $application_form; // Phiếu dự tuyển
    public $graduate_degree;// Bằng tốt nghiệp
    public $transcript; // Bảng điểm
    public $card_image; // Ảnh 3x4
    public $profile_receive_area; // Khu vực tiếp nhận hồ sơ
    public $university; // Trường
    public $gop_ban_dau; // Gộp ban đầu
    public $gop_chuyen_den; // Gộp chuyển đến
    public $gop_dang_ki; // Gộp đăng kí
    public $gop_khai_giang; // Gộp khai giảng
    public $tuan; // Tuần
    public $receive_date; // Ngày nhận hồ sơ từ TVTS
    public $delivery_date_tvu; // Ngày bàn giao TVU Cứng
    public $delivery_date_tvu_scan; // Ngày bàn giao TVU scan
    public $report_error; // Phản hồi hồ sơ lỗi giáo vụ
    public $decision_no; // Số Quyết định
    public $decision_date; // Ngày ký QĐTT
    public $decision_return_date; // Ngày GV trả QĐ cho TVTS
    public $student_card_received_date; // Ngày TVU trả thẻ SV
    public $delivery_student_profile_date; // Ngày giáo vụ BGHS trường
    public $subject; // Tổ hợp xét tuyển
    public $subject_code; // Mã hợp xét tuyển
    public $grade_1; // Điểm tổ hợp 1
    public $grade_2; // Điểm tổ hợp 2
    public $grade_3; // Điểm tổ hợp 3
    public $grade_subject; // Điểm xét tuyển
    public $grade_avg_subject; // Điểm TB TC,CĐ,ĐH
    public $rank_subject; // Xếp loại tốt nghiệp
    public $profile_status_tkts; // Trạng thái HS TKTS
    public $date_delivery_document_admission; // Ngày bàn giao hồ sơ xét tuyển
}
