<?php

namespace App\Eloquent;

use App\Eloquent\Traits\SoftDeletedTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Petition
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
 * @property $new_content
 * @property $petitionFlows
 */
class Petition extends Model
{
    use SoftDeletedTime;
    protected $table = 'petitions';

    protected $fillable = [
        'student_id',
        'content_type',
        'current_content',
        'new_content',
        'status',
        'effective_date',
        'storage_file_id',
        'date_of_amendment',
        'no',
        'created_by',
        'updated_by',
    ];

    /**
     * @return HasOne
     */
    public function latestPetitionFlow(): HasOne
    {
        return $this->hasOne(PetitionFlow::class)->latestOfMany();
    }

    /**
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class,'student_id','id');
    }

    /**
     * @return HasMany
     */
    public function petitionFlows(): HasMany
    {
        return $this->hasMany(PetitionFlow::class);
    }
}