<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class ReceivablePurpose extends Enum
{
    public const ADMISSION_FEE        = 0; // LỆ PHÍ XÉT TUYỂN
    public const TUITION_FEE          = 1; //HỌC PHÍ
    public const FEE_FOR_LEARN_AGAIN  = 2; // PHÍ HỌC LẠI
    public const RETEST_FEE           = 3; // PHÍ THI LẠI
    public const FEE_FOR_SKIP_SUBJECT = 4; // PHÍ MIỄN MÔN
    public const GRADUATE_FEE         = 5; // LỆ PHÍ TỐT NGHIỆP
    public const WITHDRAWAL_FEE       = 6; // RÚT HỌC PHÍ
    public const STUDENT_CARD_FEE     = 7; // PHÍ LÀM THẺ SINH VIÊN
}
