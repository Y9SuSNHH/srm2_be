<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;
/**
 * Class District
 * @package App\Eloquent
 */
class District extends Model
{
    
    protected $table = 'district';

    protected $fillable = [
        'id',
        'code',
        'name',
        'city',
    ];
}
