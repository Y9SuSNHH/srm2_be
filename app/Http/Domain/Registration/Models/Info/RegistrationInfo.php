<?php

namespace App\Http\Domain\Registration\Models\Info;

use App\Eloquent\Registration as RegistrationModel;
use App\Helpers\Json;
use App\Http\Enum\Deegree;
use App\Http\Enum\AdmissionObject;
use App\Http\Enum\ObjectClassification;

class RegistrationInfo extends Json
{ 
    public $id;
    public $firstname;
    public $lastname;
    public $phone_number;
    public $email;
    public $identification;
    public $identification_info;
    public $date_of_birth;
    public $year_of_birth;
    public $place_of_birth;
    public $residence;
    public $address;
    public $gender;
    public $ethnic;
    public $religion;
    public $graduate;
    public $area_id;
    public $major;
    public $major_id;
    public $curriculum_vitae;
    public $staff_fullname;
    public $week;
    public $phase;
    public $firstDay;
    public $national;
    public $updated_at;

    public function __construct(RegistrationModel $registration)
    {        
        $graduate = json_decode($registration->graduate);

        parent::__construct(array_merge($registration->toArray(), [
            'date_of_birth' => date('Y-m-d', strtotime($registration->date_of_birth)),
            'year_of_birth' => $registration->year_of_birth,
            'place_of_birth' => $registration->place_of_birth,
            'curriculum_vitae' =>json_decode($registration->curriculum_vitae),
            'identification' => $registration->identification,
            'identification_info' => json_decode($registration->identification_info),
            'residence' => json_decode($registration->residence),
            'address' => json_decode($registration->address),
            'graduate' => $graduate,
            'area_id'=> $registration->area->id,
            'major'=> $registration->major->name,
            'major_id'=> $registration->major->id,
            'staff_fullname'=> $registration->staff->fullname,
        ]));
    }
}