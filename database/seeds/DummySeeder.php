<?php

namespace Database\Seeders;

use App\Eloquent\Area;
use App\Eloquent\EnrollmentObject;
use App\Eloquent\Major;
use App\Eloquent\MajorObjectMap;
use App\Eloquent\School;

/**
 * Class DummySeeder
 * @package Database\Seeders
 */
class DummySeeder extends \Illuminate\Database\Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var School $school */
        $school = School::query()->create([
            'school_code' => 'TVU',
            'school_name' => 'Trà Vinh',
            'service_name' => 'TVU',
            'priority' => 10,
        ]);

        School::query()->create([
            'school_code' => 'UNETI',
            'school_name' => 'UNETI',
            'service_name' => 'UNETI',
        ]);

        $area_hn = Area::query()->create(['school_id' => $school->id, 'code' => 'HN', 'name' => 'HN']);
        $area_hcm = Area::query()->create(['school_id' => $school->id, 'code' => 'HCM', 'name' => 'HCM']);
        $area_tv = Area::query()->create(['school_id' => $school->id, 'code' => 'TV', 'name' => 'TV']);

        $dx = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'DX', 'name' => 'THPT', 'shortcode' => 'THPT'])->id;
        $dt = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'DT', 'name' => 'Liên thông từ trung cấp', 'shortcode' => 'TC'])->id;
        $dt_c = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'DT', 'classification' => 'C', 'name' => 'Liên thông từ trung cấp cùng ngành', 'shortcode' => 'TCCN'])->id;
        $dt_g = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'DT', 'classification' => 'G', 'name' => 'Liên thông từ trung cấp gần ngành', 'shortcode' => 'TCGN'])->id;
        $dt_k = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'DT', 'classification' => 'K', 'name' => 'Liên thông từ trung cấp khác ngành', 'shortcode' => 'TCKNT'])->id;
        $dk = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'DK', 'name' => 'Liên thông từ cao đẳng', 'shortcode' => 'CĐ'])->id;
        $dk_c = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'DK', 'classification' => 'C', 'name' => 'Liên thông từ cao đẳng cùng ngành', 'shortcode' => 'CĐC'])->id;
        $dk_g = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'DK', 'classification' => 'G', 'name' => 'Liên thông từ cao đẳng gần ngành', 'shortcode' => 'CĐG'])->id;
        $dk_k = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'DK', 'classification' => 'K', 'name' => 'Liên thông từ cao đẳng khác ngành', 'shortcode' => 'CĐK'])->id;
        $vx = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'VX', 'name' => 'VB2', 'shortcode' => 'VB2'])->id;
        $vx_g = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'VX', 'classification' => 'G', 'name' => 'VB2 gần ngành', 'shortcode' => 'VB2G'])->id;
        $vx_k = EnrollmentObject::query()->create(['school_id' => $school->id, 'code' => 'VX', 'classification' => 'K', 'name' => 'VB2 khác ngành', 'shortcode' => 'VB2K'])->id;

        $qkd = Major::query()->create(['school_id' => $school->id, 'code' => 'QKD', 'name' => 'Quản trị kinh doanh', 'shortcode' => 'QTKD'])->id;
        $kt = Major::query()->create(['school_id' => $school->id, 'code' => 'KT', 'name' => 'Kế toán', 'shortcode' => 'Kế toán'])->id;
        $nna = Major::query()->create(['school_id' => $school->id, 'code' => 'NNA', 'name' => 'Ngôn ngữ anh', 'shortcode' => 'NNA'])->id;
        $tt = Major::query()->create(['school_id' => $school->id, 'code' => 'TT', 'name' => 'Công nghệ thông tin', 'shortcode' => 'CNTT'])->id;
        $l = Major::query()->create(['school_id' => $school->id, 'code' => 'L', 'name' => 'Luật', 'shortcode' => 'Luật'])->id;
        $tc = Major::query()->create(['school_id' => $school->id, 'code' => 'TC', 'name' => 'Tài chính - Ngân hàng', 'shortcode' => 'TCNH'])->id;

        MajorObjectMap::query()->insert([
            ['major_id' => $nna, 'enrollment_object_id' => $dx],
            ['major_id' => $nna, 'enrollment_object_id' => $dt],
            ['major_id' => $nna, 'enrollment_object_id' => $dk],
            ['major_id' => $nna, 'enrollment_object_id' => $vx],

            ['major_id' => $tc, 'enrollment_object_id' => $dx],
            ['major_id' => $tc, 'enrollment_object_id' => $dt],
            ['major_id' => $tc, 'enrollment_object_id' => $dk],
            ['major_id' => $tc, 'enrollment_object_id' => $vx],

            ['major_id' => $qkd, 'enrollment_object_id' => $dt_c],
            ['major_id' => $qkd, 'enrollment_object_id' => $dt_g],
            ['major_id' => $qkd, 'enrollment_object_id' => $dt_k],
            ['major_id' => $qkd, 'enrollment_object_id' => $vx_g],
            ['major_id' => $qkd, 'enrollment_object_id' => $vx_k],
            ['major_id' => $qkd, 'enrollment_object_id' => $dk_c],
            ['major_id' => $qkd, 'enrollment_object_id' => $dk_g],
            ['major_id' => $qkd, 'enrollment_object_id' => $dk_k],

            ['major_id' => $kt, 'enrollment_object_id' => $dt_c],
            ['major_id' => $kt, 'enrollment_object_id' => $dt_g],
            ['major_id' => $kt, 'enrollment_object_id' => $dt_k],
            ['major_id' => $kt, 'enrollment_object_id' => $dk_c],
            ['major_id' => $kt, 'enrollment_object_id' => $dk_g],
            ['major_id' => $kt, 'enrollment_object_id' => $dk_k],

            ['major_id' => $l, 'enrollment_object_id' => $dt_c],
            ['major_id' => $l, 'enrollment_object_id' => $dt_g],
            ['major_id' => $l, 'enrollment_object_id' => $dt_k],

            ['major_id' => $tt, 'enrollment_object_id' => $dt_c],
            ['major_id' => $tt, 'enrollment_object_id' => $dt_k],
            ['major_id' => $tt, 'enrollment_object_id' => $dk_c],
            ['major_id' => $tt, 'enrollment_object_id' => $dk_k],

        ]);
    }
}
