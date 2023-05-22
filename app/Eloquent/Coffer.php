<?php

namespace App\Eloquent;

/**
 * Class Coffer
 * @package App\Eloquent
 *
 * @property $id
 * @property $identification
 * @property $firstname
 * @property $lastname
 * @property $birthday
 * @property $image
 * @property $unit
 * @property $amount
 * @property $debit
 * @property $credit
 * @property $created_by
 * @property $updated_by
 *
 * @property Profile[]|\Illuminate\Database\Eloquent\Collection $profiles
 */
class Coffer extends Model
{
    protected $table = 'coffers';

    protected $fillable = [
        'identification',
        'firstname',
        'lastname',
        'birthday',
        'image',
        'unit',
        'amount',
        'debit',
        'credit',
        'created_by',
        'updated_by',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function profiles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_coffers');
    }
}