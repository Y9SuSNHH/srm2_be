<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Area
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property string $code
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 */
class Area extends Model
{
    use SoftDeletedTime, HasSchool;

    protected $table = 'areas';

    protected $fillable = [
        'school_id',
        'code',
        'name',
        'created_by',
        'updated_by',
    ];

    /**
     * @return HasMany
     */
    public function enrollmentWaves(): HasMany
    {
        return $this->hasMany(EnrollmentWave::class);
    }
}
