<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ClassroomReceivable extends Model
{
    protected $table = 'classroom_receivables';

    protected $fillable = [
        'classroom_id',
        'semester',
        'purpose',
        'fee',
        'began_date',
        'ended_date',
        'created_by',
        'updated_id'
    ];

    protected $dates = [
        'began_date',
        'ended_date',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}