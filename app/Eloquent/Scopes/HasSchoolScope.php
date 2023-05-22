<?php

namespace App\Eloquent\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class HasSchoolScope
 * @package App\Eloquent\Scopes
 */
class HasSchoolScope implements Scope
{
    /**
     * @param Builder $builder
     * @param Model $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where("{$model->getTable()}.school_id", school()->getId());
    }
}
