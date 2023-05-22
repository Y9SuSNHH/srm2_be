<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class LearningEngagementProcess extends Model
{
    protected $table = 'learning_engagement_processes';

    protected $fillable = [
        'id',
        'student_id',
        'last_modified',
        'modified_by',
        'is_join_first_day_of_school',
        'is_join_first_week',
        'is_join_fourth_week',
        'student_type_first_week',
        'student_type_fourth_week',
    ];
}