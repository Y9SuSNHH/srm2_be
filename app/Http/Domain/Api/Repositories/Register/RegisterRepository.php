<?php

namespace App\Http\Domain\Api\Repositories\Register;

use App\Eloquent\Registration;
use App\Eloquent\BlacklistToken;
use Illuminate\Http\Exceptions\HttpResponseException;


class RegisterRepository implements RegisterRepositoryInterface
{
    public function register(array $request) :array
    {
        try {
            $array_name = explode(' ', $request['fullname']);
            $request['lastname'] = trim(array_pop($array_name));
            $request['firstname']  = trim(implode(' ', $array_name));
            $identification_info = [
                'date'=> $request['identification_date'],
                'place'=> $request['identification_place'],
            ];

            $residence = [
                'street'=> $request['residence_street'],
                'ward'=> $request['residence_ward'],
                'district'=> $request['residence_district'],
                'province'=> $request['residence_province'],
            ];

            $address = [
                'street'=> $request['address_street'],
                'ward'=> $request['address_ward'],
                'district'=> $request['address_district'],
                'province'=> $request['address_province'],
            ];
            
            $graduate = [
                'deegree'=> $request['deegree'],
                'specializaition'=> $request['specializaition'],
                'admission_object'=> "",
                'object_classfication'=> "",
            ];

            $curriculum_vitae = [
                'past'=>[
                    'school'=> $request['past_school'],
                    'province'=> $request['past_province'],
                    'district'=> $request['past_district'],
                ],
                'now'=>[
                    'job'=> $request['now_job'],
                    'location'=> $request['now_location'] ?? "",
                ],
                'representavie'=>[
                    'first'=>[
                        'name'=> $request['first_name'],
                        'relation'=> $request['first_relation'],
                        'job'=> $request['first_job'],
                        'phone'=> $request['first_phone'],
                        'street'=> $request['first_street'],
                        'province'=> $request['first_province'],
                        'district'=> $request['first_district'],
                        'ward'=> $request['first_ward'],
                    ],
                    'second'=>[
                        'name'=> $request['second_name'] ?? "",
                        'relation'=> $request['second_relation'] ?? "",
                        'job'=> $request['second_job'] ?? "",
                        'phone'=> $request['second_phone'] ?? "",
                        'street'=> $request['second_street'] ?? "",
                        'province'=> $request['second_province'] ?? "",
                        'district'=> $request['second_district'] ?? "",
                        'ward'=> $request['second_ward'] ?? "",
                    ]
                ],
            ];

            $request['identification_info'] = json_encode($identification_info);
            $request['residence'] = json_encode($residence);
            $request['address'] = json_encode($address);
            $request['graduate'] = json_encode($graduate);
            $request['curriculum_vitae'] = json_encode($curriculum_vitae);
            $registration = Registration::create($request);
            if ($registration->exists) {
                BlacklistToken::query()->create(['signature' => $request['block_token']]);
                return [];
             } else {
                throw new \Exception('Error');
             }
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'errors' => 'Error']));
        }
    }
}
