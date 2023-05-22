<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Area
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property \Carbon\Carbon $effective_date
 * @property int $price
 * @property bool $lock
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 */
class CreditPrice extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'credit_prices';

    protected $fillable = [
        'school_id',
        'effective_date',
        'price',
        'lock',
        'created_by',
        'updated_by',
    ];

    protected $dates = [
        'effective_date',
    ];
}
