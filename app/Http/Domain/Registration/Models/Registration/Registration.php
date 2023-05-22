<?php

namespace App\Http\Domain\Registration\Models\Registration;

use App\Eloquent\Registration as RegistrationModel;
use App\Helpers\Json;
use App\Http\Enum\Deegree;

class Registration extends Json
{ 
    public $id;
    public $fullname;
    public $phone_number;
    public $identification;
    public $dateofbirth;
    public $deegree;
    public $area;
    public $major;
    public $school;
    public $curriculum_vitae;

    public function __construct(RegistrationModel $registration)
    {        
        $graduate = json_decode($registration->graduate);

        parent::__construct(array_merge($registration->toArray(), [
            'fullname' => $registration->firstname . ' ' . $registration->lastname,
            'dateofbirth' => date('d/m/Y', strtotime($registration->date_of_birth)),
            'identification' => $registration->identification,
            'deegree' => Deegree::from(intval($graduate->deegree))->getLang(),
            'area'=> $registration->area?->code ?? '',
            'major'=> $registration->major?->name ?? '',
            'school'=> school()->getCode(),
        ]));
    }
}