<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;
use App\Eloquent\Traits\SoftDeletedTime;
use App\Eloquent\Traits\HasSchool;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Class Contact
 * @package App\Eloquent
 *
 * @property int $id
 * @property int $school_id
 * @property string $firstname
 * @property string $lastname
 * @property string $phone_number
 * @property string $email
 * @property string $source
 * @property string $link
 * @property int $status
 */
class Contact extends Model
{
    use SoftDeletedTime;
    
    protected $table = 'sv50_contacts';

    protected $fillable = [
        'id',
        'school_id',
        'staff_id',
        'firstname',
        'lastname',
        'phone_number',
        'email',
        'source',
        'link',
        'status',
        'staff_info',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
