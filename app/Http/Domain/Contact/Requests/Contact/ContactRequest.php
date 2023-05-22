<?php

namespace App\Http\Domain\Contact\Requests\Contact;

use App\Helpers\Request;

/**
 * Class ContactRequest
 * @package App\Http\Domain\Contact\Requests\Contact
 *
 * @property array $items
 */
class ContactRequest extends Request
{
//    /**
//     * @var array
//     */
//    protected $casts = [
//        'fullname'      => Request::CAST_STRING,
//        'school_id'     => Request::CAST_INT,
//        'email'         => Request::CAST_STRING,
//        'source'        => Request::CAST_STRING,
//        'phoneNumber'   => Request::CAST_STRING,
//        'staff_info'    => Request::CAST_STRING
//    ];

    /**
     * @param array $input
     * @return array
     */
    public function rules(array $input): array
    {
        return [
            'items' => 'required|array',
            'items.*.fullname'      => 'required|string',
            'items.*.school_id'     => 'required|int',
            'items.*.email'         => 'string',
            'items.*.source'        => 'string',
            'items.*.phone_number'   => 'required|string',
            'items.*.staff_info'   => 'string',
        ];
    }

}