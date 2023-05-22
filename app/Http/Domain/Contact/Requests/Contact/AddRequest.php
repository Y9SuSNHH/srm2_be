<?php

namespace App\Http\Domain\Contact\Requests\Contact;

use App\Helpers\Request;

/**
 * Class AddRequest
 * @package App\Http\Domain\Contact\Requests\Contact
 *
 * @property array $items
 */
class AddRequest extends Request
{
   /**
    * @var array
    */
   protected $casts = [
       'fullname'      => Request::CAST_STRING,
       'school_id'     => Request::CAST_INT,
       'email'         => Request::CAST_STRING,
       'source'        => Request::CAST_STRING,
       'phoneNumber'   => Request::CAST_STRING,
   ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'fullname'      => 'required|string',
            'school_id'     => 'required|int',
            'email'         => 'string',
            'source'        => 'string',
            'phone_number'   => 'required|string',
        ];
    }

}