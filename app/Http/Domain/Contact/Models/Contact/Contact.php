<?php

namespace App\Http\Domain\Contact\Models\Contact;

use App\Eloquent\Contact as ContactModel;
use App\Helpers\Json;

class Contact extends Json
{ 
    public $id;
    public $fullname;
    public $phone_number;
    public $email;
    public $source;
    public $link;
    public $status;
    public $school;

    public function __construct(ContactModel $contact)
    {        
        parent::__construct(array_merge($contact->toArray(), [
            'fullname' => $contact->firstname . ' ' . $contact->lastname,
            'school' => [
                'school_code' => $contact->school->school_code,
                'school_id' => $contact->school_id
            ]
        ]));
    }
}