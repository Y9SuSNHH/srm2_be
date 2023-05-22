<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * Class Profile
 * @package App\Eloquent
 *
 * @property $id
 * @property $firstname
 * @property $lastname
 * @property $gender
 * @property $identification
 * @property $identification_div
 * @property $grant_date
 * @property $grant_place
 * @property $main_residence
 * @property $birthday
 * @property $borned_year
 * @property $borned_place
 * @property $phone_number
 * @property $nation
 * @property $religion
 * @property $email
 * @property $address
 * @property $curriculum_vitae
 * @property $created_at
 * @property $created_by
 * @property $updated_at
 * @property $updated_by
 *
 * @property Coffer[]|\Illuminate\Database\Eloquent\Collection $coffers
 */
class Profile extends Model
{
    protected $table = 'profiles';

    protected $fillable = [
        'id',
        'firstname',
        'lastname',
        'gender',
        'identification',
        'identification_div',
        'grant_date',
        'grant_place',
        'main_residence',
        'birthday',
        'borned_year',
        'borned_place',
        'phone_number',
        'nation',
        'religion',
        'email',
        'address',
        'curriculum_vitae',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    /**
     * @return HasOneThrough
     */
    public function student(): HasOneThrough
    {
        return $this->hasOneThrough(Student::class, StudentProfile::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function coffers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Coffer::class, 'profile_coffers');
    }
}