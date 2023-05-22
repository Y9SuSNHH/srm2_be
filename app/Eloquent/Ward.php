<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;
/**
 * Class Ward
 * @package App\Eloquent
 */
class Ward extends Model
{
    
    protected $table = 'ward';

    protected $fillable = [
        'id',
        'code',
        'name',
        'district',
    ];
}
