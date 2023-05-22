<?php

namespace App\Http\Domain\Student\Services;

use App\Helpers\CsvParser;

class ExportStudentProfileService
{
    /**
     * @return array
     */
    public function getLabels(): array
    {
    return [
            'A' => 'Mã hồ sơ', 
            'B' => 'Ngày GV trả QĐ cho TVTS', 
            'C' => 'Trạng thái Hồ sơ giáo vụ', 
            'D' => 'Khu vực tiếp nhận hồ sơ (HCM/HN)', 
            'E' => 'Ngày nhận hồ sơ từ TVTS', 
            'F' => 'Ngày TVU trả thẻ SV', 
            'G' => 'Trạng thái HS TKTS', 
            'H' => 'Phản hồi hồ sơ lỗi giáo vụ', 
            'I' => 'Dân tộc', 
            'J' => 'Tôn giáo', 
            'K' => 'Số CMND', 
            'L' => 'Ngày cấp', 
            'M' => 'Nơi cấp', 
            'N' => 'Trường học lớp 12 bậc THPT', 
            'O' => 'Quận/huyện của Trường học lớp 12 bậc THPT', 
            'P' => 'Tỉnh/TP của Trường học lớp 12 bậc THPT', 
            'Q' => 'Công việc hiện nay -Cơ quan công tác', 
            'R' => 'Người đại diện 1', 
            'S' => 'Quan hệ', 
            'T' => 'Nghề nghiệp', 
            'U' => 'Điện thoại', 
            'V' => 'Địa chỉ', 
            'W' => 'Người đại diện 2', 
            'X' => 'Quan hệ', 
            'Y' => 'Nghề nghiệp', 
            'Z' => 'Điện thoại', 
            'AA' => 'Địa chỉ', 
            'AB' => 'Ghi chú', 
            'AC' => 'Account học tập (áp dụng UNETI)', 
            'AD' => 'Email học tập (áp dụng UNETI)', 
            'AE' => 'Mã tổ hợp xét tuyển', 
            'AF' => 'Điểm TH1', 
            'AG' => 'Điểm TH2', 
            'AH' => 'Điểm TH3', 
            'AI' => 'Điểm TB TC, CĐ, ĐH', 
            'AJ' => 'Điểm XT', 
            'AK' => 'Xếp loại tốt nghiệp', 
            'AL' => 'Cơ quan công tác',
            'AM' => 'Mã sinh viên' 
        ];
    }

    /**
     * @return array
     */
    public function createTemplateFile(): array
    {
        $tmp_file = CsvParser::createCsvUTF8BOMTmp([self::getLabels(), []]);

        return $tmp_file ? stream_get_meta_data($tmp_file) : [];
    }
}
