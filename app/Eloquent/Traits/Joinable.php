<?php

namespace App\Eloquent\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

trait Joinable
{
    public function scopeJoinRelation(Builder $query, $relation)
    {
        $relation = $this->$relation();
        if ($relation instanceof Relation) {
            $table = $relation->getRelated()->getTable();
            $one   = $relation->getQualifiedParentKeyName();
            $two   = $relation->getQualifiedForeignKeyName();
            return $query->join($table, $one, '=', $two);
        }
    }
}