<?php


namespace App\Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class MajorObjectMap
 * @package App\Eloquent
 *
 * @property Major $major
 * @property EnrollmentObject $enrollment_object
 */
class MajorObjectMap extends Model
{
    protected $table = 'major_object_maps';

    protected $fillable = [
        'major_id',
        'enrollment_object_id',
        // 'object_classification_id',
        'created_by',
        'updated_by',
    ];
    /**
     * @return BelongsTo
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'major_id', 'id');
    }
    public function enrollment_object(): BelongsTo
    {
        return $this->belongsTo(EnrollmentObject::class, 'enrollment_object_id', 'id');
    }
}