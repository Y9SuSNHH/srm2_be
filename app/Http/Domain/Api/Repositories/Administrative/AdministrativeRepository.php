<?php

namespace App\Http\Domain\Api\Repositories\Administrative;
use App\Eloquent\City;
use App\Eloquent\District;
use App\Eloquent\Ward;

class AdministrativeRepository implements AdministrativeRepositoryInterface
{
    public function getAll() :array
    {
        $cities = City::get(['code','name']);
        $list_district = District::get();
        $list_ward = Ward::get();

        // Get Districts
        $disricts = [];
        foreach ($list_district as $item) {
            $city = $item->city;
            $name = $item->name;
            $code = $item->code;
            
            if (!isset($disricts[$city])) {
                $disricts[$city] = [];
            }
            
            $disricts[$city][] = (object) ["name" => $name, "code" => $code];
        }
        $disricts = (object) $disricts;

        // Get Wards
        $wards = [];
        foreach ($list_ward as $item) {
            $district = $item->district;
            $name = $item->name;
            $code = $item->code;
            
            if (!isset($wards[$district])) {
                $wards[$district] = [];
            }
            
            $wards[$district][] = (object) ["name" => $name, "code" => $code];
        }
        $wards = (object) $wards;

        

        $return = [
            'city' => $cities,
            'district' => $disricts,
            'ward' => $wards,
        ];
        return $return;
    }
}
