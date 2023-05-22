<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;

class BlacklistToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'signature',
    ];
}
