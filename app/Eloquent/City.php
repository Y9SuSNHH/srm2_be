<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;
/**
 * Class City
 * @package App\Eloquent
 */
class City extends Model
{
    
    protected $table = 'city';

    protected $fillable = [
        'id',
        'code',
        'name',
    ];
}
