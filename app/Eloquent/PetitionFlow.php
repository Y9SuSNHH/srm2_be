<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PetitionFlow
 * @package App\Eloquent
 *
 * @property int $id
 * @property $student_id
 * @property $type
 * @property $current_content
 * @property $different_content
 * @property $status
 * @property $approval_status
 * @property $correction_date
 * @property $storage_file_id
 * @property $no
 * @property $reference_type
 * @property $reference_id
 * @property mixed $role_authority
 */
class PetitionFlow extends Model
{
    public const UPDATED_AT = null;
    protected $table = 'petition_flows';

    protected $fillable = [
        'petition_id',
        'staff_id',
        'status',
        'created_at',
        'note',
        'role_authority',
        'is_update_student',
    ];

    /**
     * @return belongsTo
     */
    public function petition(): belongsTo
    {
        return $this->belongsTo(Petition::class);
    }

    /**
     * @return BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}