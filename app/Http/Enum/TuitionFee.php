<?php

namespace App\Http\Enum;

use App\Helpers\Enum;

class TuitionFee extends Enum
{
    public const FREE = 'HOC_PHI';

    public const ADMISSION_FEE = 'LE_PHI_XET_TUYEN';

    public const FEE_FOR_LEARN_AGAIN = 'PHI_HOC_LAI';

    public const RETEST_FEE = 'PHI_THI_LAI';

    public const FEE_FOR_SKIP_SUBJECT = 'PHI_MIEN_MON';

    public const GRADUATE_FEE = 'LE_PHI_TOT_NGHIEP';

}
