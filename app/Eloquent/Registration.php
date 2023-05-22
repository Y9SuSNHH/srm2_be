<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;
use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Class Registration
 * @package App\Eloquent
 */
class Registration extends Model
{
    use SoftDeletedTime;
    
    protected $table = 'sv50_registration';

    protected $fillable = [
        'id',
        'firstname',
        'lastname',
        'gender',
        'identification',
        'identification_info',
        'residence',
        'date_of_birth',
        'place_of_birth',
        'phone_number',
        'ethnic',
        'religion',
        'email',
        'address',
        'graduate',
        'area_id',
        'staff_id',
        'major_id',
        'curriculum_vitae',
        'week',
        'phase',
        'firstDay',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'national',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }
}
