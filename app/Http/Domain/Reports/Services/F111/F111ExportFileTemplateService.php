<?php

namespace App\Http\Domain\Reports\Services\F111;

use App\Helpers\CsvParser;

class F111ExportFileTemplateService
{
    /**
     * @return array
     */
    public static function getLabels(): array
    {
        return [
            'A' => 'STT',
            'B' => 'TVTS',
            'C' => 'Tên TS8',
            'D' => 'Đợt khai giảng',
            'E' => 'Mã hồ sơ cũ',
            'F' => 'Mã hồ sơ',
            'G' => 'Gộp cũ',
            'H' => 'Gộp chuyển đến',
            'I' => 'Khu vực',
            'J' => 'Họ & tên',
            'K' => 'Họ đệm',
            'L' => 'Tên',
            'M' => 'Giới tính',
            'N' => 'Ngày sinh',
            'O' => 'Nơi sinh',
            'P' => 'Dân tộc',
            'Q' => 'Tôn giáo',
            'R' => 'Số CMND/CCCD',
            'S' => 'Ngày cấp',
            'T' => 'Nơi cấp',
            'U' => 'Hộ khẩu thường trú',
            'V' => 'Địa chỉ liên hệ',
            'W' => 'Điện thoại',
            'X' => 'Email cá nhân',
            'Y' => 'Đối tượng (Đã tốt nghiệp)',
            'Z' => 'Ngành Tốt Nghiệp',
            'AA' => 'Năm Tốt Nghiệp',
            'AB' => 'Nơi cấp bằng',
            'AC' => 'Trường học lớp 12 bậc THPT',
            'AD' => 'Quận/huyện của Trường học lớp 12',
            'AE' => 'Tỉnh/TP của Trường học lớp 12',
            'AF' => 'Công việc hiện nay',
            'AG' => 'Cơ quan công tác',
            'AH' => 'Họ và tên Người thân(đại diện) 1',
            'AI' => 'Mối quan hệ',
            'AJ' => 'Nghề nghiệp',
            'AK' => 'Điện thoại',
            'AL' => 'Địa chỉ',
            'AM' => 'Họ và tên Người thân(đại diện) 2',
            'AN' => 'Mối quan hệ',
            'AO' => 'Nghề nghiệp',
            'AP' => 'Điện thoại',
            'AQ' => 'Địa chỉ',
            'AR' => 'Khóa',
            'AS' => 'Ngày đăng ký',
            'AT' => 'Mã viết tắt ngành',
            'AU' => 'Mã viết tắt đối tượng',
            'AV' => 'Mã viết tắt phân loại đối tượng',
            'AW' => 'Mã lớp',
            'AX' => 'Mã lớp trên QĐTT (file excel)',
            'AY' => 'Trạng thái hồ sơ Giáo vụ',
            'AZ' => 'Ghi chú',
            'BA' => 'Level',
            'BB' => 'Mã tổ hợp xét tuyển',
            'BC' => 'Tên tổ hợp',
            'BD' => 'Đầu điểm 1',
            'BE' => 'Đầu điểm 2',
            'BF' => 'Đầu điểm 3',
            'BG' => 'Điểm xét tuyển',
            'BH' => 'Xếp loại tốt nghiệp',
            'BI' => 'Ngày gửi GV F111',
            'BJ' => 'Nhóm NNA',
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
