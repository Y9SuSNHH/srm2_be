<?php

namespace App\Eloquent\Traits;

use App\Eloquent\School;
use App\Eloquent\Scopes\HasSchoolScope;

/**
 * @method static addGlobalScope(HasSchoolScope $param)
 */
trait HasSchool
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootHasSchool()
    {
        static::addGlobalScope(new HasSchoolScope());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
