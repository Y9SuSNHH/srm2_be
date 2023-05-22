<?php

namespace App\Http\Domain\Registration\Services;

use App\Http\Domain\Registration\Repositories\Registration\RegistrationRepositoryInterface;
use App\Eloquent\Ward;
use App\Eloquent\District;
use App\Eloquent\City;


class RegistrationExportFileService
{
    private $registration_repository;

    public function __construct(RegistrationRepositoryInterface $registration_repository)
    {
        $this->registration_repository = $registration_repository;
    }

    public function generateFile(int $id)
    {
        $data_registration = $this->registration_repository->getById($id);
        $major = $data_registration['major'];

        $deegree = [
            1 => 'THPT',
            2 => 'TC',
            3 => 'CĐ',
            4 => 'ĐH'
        ];
        $deegreeid = $data_registration['graduate']->deegree;
        $deegreename = $deegree[$deegreeid];

        $firstname = $data_registration['firstname'];
        $lastname = $data_registration['lastname'];
        $fullname = $firstname . ' ' . $lastname;

        $gender = [
            0 => 'Nam',
            1 => 'Nữ'
        ];
        $genderid = $data_registration['gender'];
        $gendername = $gender[$genderid];

        $date_of_birth = date('d/m/Y', strtotime($data_registration['date_of_birth']));

        $place_of_birth = $data_registration['place_of_birth'];

        $ethnic = $data_registration['ethnic'];

        $religion = $data_registration['religion'];

        $national = $data_registration['national'];

        $identification = $data_registration['identification'];

        $identification_info_date = date('d/m/Y', strtotime($data_registration['identification_info']->date));
        $identification_info_place = $data_registration['identification_info']->place;

        $street = $data_registration['residence']->street;

        $ward_code = $data_registration['residence']->ward;
        $ward_name = Ward::where('code', $ward_code)->first()->name;

        $district_code = $data_registration['residence']->district;
        $district_name = District::where('code', $district_code)->first()->name;

        $city_code = $data_registration['residence']->province;
        $city_name = City::where('code', $city_code)->first()->name;

        $residence = $street . ', ' . $ward_name . ', ' . $district_name . ', ' . $city_name;
        $specializaition = $data_registration['graduate']->specializaition;

        $past_school = $data_registration['curriculum_vitae']->past->school;

        $past_district_code = $data_registration['curriculum_vitae']->past->district;
        $past_district_name = District::where('code', $past_district_code)->first()->name;

        $past_city_code = $data_registration['curriculum_vitae']->past->province;
        $past_city_name = City::where('code', $past_city_code)->first()->name;

        $past = $past_school . ', Tại Quận/huyện: ' . $past_district_name . ', Tỉnh/TP: ' . $past_city_name;

        $now_job = $data_registration['curriculum_vitae']->now->job;
        $now_location = $data_registration['curriculum_vitae']->now->location;

        $phone_number = $data_registration['phone_number'];

        $email = $data_registration['email'];

        $address_street = $data_registration['address']->street;

        $address_ward_code = $data_registration['address']->ward;
        $address_ward_name = Ward::where('code', $address_ward_code)->first()->name;

        $address_district_code = $data_registration['address']->district;
        $address_district_name = District::where('code', $address_district_code)->first()->name;

        $address_province_code = $data_registration['address']->province;
        $address_province_name = City::where('code', $address_province_code)->first()->name;

        $address = $address_street . ', ' . $address_ward_name . ', ' . $address_district_name . ', ' . $address_province_name;

        $representavie_first_name = $data_registration['curriculum_vitae']->representavie->first->name;
        $representavie_first_relation = $data_registration['curriculum_vitae']->representavie->first->relation;
        $representavie_first_job = $data_registration['curriculum_vitae']->representavie->first->job;
        $representavie_first_phone = $data_registration['curriculum_vitae']->representavie->first->phone;

        $representavie_first_street = $data_registration['curriculum_vitae']->representavie->first->street;

        $representavie_first_ward_code = $data_registration['curriculum_vitae']->representavie->first->ward;
        $representavie_first_ward_name = Ward::where('code', $representavie_first_ward_code)->first()->name;

        $representavie_first_district_code = $data_registration['curriculum_vitae']->representavie->first->district;
        $representavie_first_district_name = District::where('code', $representavie_first_district_code)->first()->name;

        $representavie_first_province_code = $data_registration['curriculum_vitae']->representavie->first->province;
        $representavie_first_province_name = City::where('code', $representavie_first_province_code)->first()->name;

        $representavie_first_address = $representavie_first_street . ', ' . $representavie_first_ward_name . ', ' . $representavie_first_district_name . ', ' . $representavie_first_province_name;

        $representavie_second_name = $data_registration['curriculum_vitae']->representavie->second->name;
        $representavie_second_relation = $data_registration['curriculum_vitae']->representavie->second->relation;
        $representavie_second_job = $data_registration['curriculum_vitae']->representavie->second->job;
        $representavie_second_phone = $data_registration['curriculum_vitae']->representavie->second->phone;

        $representavie_second_street = $data_registration['curriculum_vitae']->representavie->second->street;

        $representavie_second_ward_code = $data_registration['curriculum_vitae']->representavie->second->ward;
        $representavie_second_ward_name = $representavie_second_ward_code != null ? ', ' . Ward::where('code', $representavie_second_ward_code)->first()->name : '';

        $representavie_second_district_code = $data_registration['curriculum_vitae']->representavie->second->district;
        $representavie_second_district_name = $representavie_second_district_code != null ? ', ' . District::where('code', $representavie_second_district_code)->first()->name : '';

        $representavie_second_province_code = $data_registration['curriculum_vitae']->representavie->second->province;
        $representavie_second_province_name = $representavie_second_province_code != null ? ', ' .  City::where('code', $representavie_second_province_code)->first()->name : '';

        $representavie_second_address = $representavie_second_street . $representavie_second_ward_name . $representavie_second_district_name . $representavie_second_province_name;

        $school = $data_registration['school'];

        $file_content_tvu_header = file_get_contents(storage_path(sprintf('app/template/%s', 'template-registration-tvu-header.fodt')));
        $file_content_tvu_body_footer = file_get_contents(storage_path(sprintf('app/template/%s', 'template-registration-tvu-body.fodt')));

        $file_content_uneti_header = file_get_contents(storage_path(sprintf('app/template/%s', 'template-registration-uneti-header.fodt')));
        $file_content_uneti_footer = file_get_contents(storage_path(sprintf('app/template/%s', 'template-registration-uneti-footer.fodt')));

        $file_content_tvu_body_header = '
        <text:p text:style-name="P24">Ngành: ' . $major . '</text:p>
        <text:p text:style-name="P25"/>
        <text:p text:style-name="P24">Đối tượng: từ (THPT/TC/CĐ/ĐH) ' . $deegreename . ' học lên Đại học<text:span text:style-name="T3"/></text:p>
        <text:p text:style-name="P22"/>
        <text:p text:style-name="P10"><text:s text:c="4"/></text:p>
        <text:p text:style-name="P12">1. BẢN THÂN<text:span text:style-name="T13"/></text:p>
        <table:table table:name="Table2" table:style-name="Table2">
         <table:table-column table:style-name="Table2.A"/>
         <table:table-column table:style-name="Table2.B"/>
         <table:table-column table:style-name="Table2.C"/>
         <table:table-column table:style-name="Table2.D"/>
         <table:table-column table:style-name="Table2.E"/>
         <table:table-column table:style-name="Table2.F"/>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="5" office:value-type="string">
           <text:p text:style-name="P26"><text:span text:style-name="T13">Họ và tên khai sinh:</text:span> <text:span text:style-name="T13">' . $fullname . '</text:span></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
           <text:p text:style-name="P26"><text:span text:style-name="T13">Giới tính: ' . $gendername . '</text:span></text:p>
          </table:table-cell>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="3" office:value-type="string">
           <text:p text:style-name="P11">Ngày sinh: ' . $date_of_birth . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="3" office:value-type="string">
           <text:p text:style-name="P11">Nơi sinh: ' . $place_of_birth . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
           <text:p text:style-name="P11">Dân tộc: ' . $ethnic . '</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="3" office:value-type="string">
           <text:p text:style-name="P11">Tôn giáo: ' . $religion . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="2" office:value-type="string">
           <text:p text:style-name="P11">Quốc tịch: ' . $national . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="3" office:value-type="string">
           <text:p text:style-name="P34">Số CMND/CCCD: ' . $identification . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="3" office:value-type="string">
           <text:p text:style-name="P11">Ngày cấp: ' . $identification_info_date . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="6" office:value-type="string">
           <text:p text:style-name="P11">Nơi cấp: ' . $identification_info_place . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="6" office:value-type="string">
           <text:p text:style-name="P11">Hộ khẩu thường trú: ' . $residence . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="6" office:value-type="string">
           <text:p text:style-name="P11">Bằng tốt nghiệp sử dụng đăng ký xét tuyển: ' . $deegreename . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="6" office:value-type="string">
           <text:p text:style-name="P11">Ngành tốt nghiệp TC/CĐ/ĐH: ' . $specializaition . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="6" office:value-type="string">
           <text:p text:style-name="P11">Nơi học lớp 12 bậc THPT: ' . $past . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="6" office:value-type="string">
           <text:p text:style-name="P11">Công việc hiện nay: ' . $now_job . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="6" office:value-type="string">
           <text:p text:style-name="P11">Đơn vị công tác: ' . $now_location . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="2" office:value-type="string">
           <text:p text:style-name="P11">Điện thoại: ' . $phone_number . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="4" office:value-type="string">
           <text:p text:style-name="P11">E-mail: ' . $email . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="6" office:value-type="string">
           <text:p text:style-name="P26"><text:span text:style-name="T13">Địa chỉ liên hệ (</text:span><text:span text:style-name="T15">Học viên ghi rõ để nhận thông báo</text:span><text:span text:style-name="T13">): ' . $address . '</text:span></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
         </table:table-row>
        </table:table>
        <text:p text:style-name="P13">2. NGƯỜI THÂN (ĐẠI DIỆN) CÓ THỂ LIÊN LẠC KHI CẦN<text:span text:style-name="T19"/></text:p>
        <text:p text:style-name="P18"/>
        <table:table table:name="Table3" table:style-name="Table3">
         <table:table-column table:style-name="Table3.A"/>
         <table:table-column table:style-name="Table3.B"/>
         <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
           <text:p text:style-name="P17">Họ và tên : ' . $representavie_first_name . '</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
           <text:p text:style-name="P14">Quan hệ: ' . $representavie_first_relation . '</text:p>
          </table:table-cell>
         </table:table-row>
         <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
           <text:p text:style-name="P15">Nghề nghiệp: ' . $representavie_first_job . '</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
           <text:p text:style-name="P14">Điện thoại: ' . $representavie_first_phone . '</text:p>
          </table:table-cell>
         </table:table-row>
         <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A1" table:number-columns-spanned="2" office:value-type="string">
           <text:p text:style-name="P15">Địa chỉ : ' . $representavie_first_address . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
         </table:table-row>
         <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
           <text:p text:style-name="P17">Họ và tên : ' . $representavie_second_name . '</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
           <text:p text:style-name="P14">Quan hệ: ' . $representavie_second_relation . '</text:p>
          </table:table-cell>
         </table:table-row>
         <text:soft-page-break/>
         <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
           <text:p text:style-name="P15">Nghề nghiệp: ' . $representavie_second_job . '</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
           <text:p text:style-name="P14">Điện thoại: ' . $representavie_second_phone . '</text:p>
          </table:table-cell>
         </table:table-row>
         <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A1" table:number-columns-spanned="2" office:value-type="string">
           <text:p text:style-name="P15">Địa chỉ : ' . $representavie_second_address . '</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
         </table:table-row>
        </table:table>
        ';

        $file_content_tvu_footer = '
                    <table:table-cell table:style-name="Table4.A1" office:value-type="string">
                    <text:p text:style-name="P29">' . $fullname . '</text:p>
                </table:table-cell>
                </table:table-row>
                </table:table>
                <text:p text:style-name="P16"/>
                </office:text>
            </office:body>
            </office:document>
        ';

        $file_content_uneti_body = '
            <table:table table:name="Table2" table:style-name="Table2">
            <table:table-column table:style-name="Table2.A"/>
            <table:table-column table:style-name="Table2.B"/>
            <table:table-column table:style-name="Table2.C"/>
            <table:table-column table:style-name="Table2.D"/>
            <table:table-column table:style-name="Table2.E"/>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="4" office:value-type="string">
            <text:p text:style-name="P17"><text:span text:style-name="T4">1. Họ và tên</text:span><text:span text:style-name="T12"> (viết chữ in hoa): ' . $fullname . '</text:span><text:span text:style-name="T2"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">Giới tính: ' . $gendername . '</text:span><text:span text:style-name="T2"/></text:p>
            </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P17"><text:span text:style-name="T4">2. </text:span><text:span text:style-name="T12">Ngày sinh:</text:span><text:span text:style-name="T11"> ' . $date_of_birth . '</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">Nơi sinh: ' . $place_of_birth . '</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">Dân tộc: ' . $ethnic . '</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P17"><text:span text:style-name="T4">3. </text:span><text:span text:style-name="T12">Số CMTND/Hộ chiếu:</text:span><text:span text:style-name="T11"> ' . $identification . '</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">Ngày cấp: ' . $identification_info_date . '</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">Nơi cấp: ' . $identification_info_place . '</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P17"><text:span text:style-name="T4">4. </text:span><text:span text:style-name="T12">Điện thoại di động:</text:span><text:span text:style-name="T11"> ' . $phone_number . '</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">Email: ' . $email . '</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="5" office:value-type="string">
            <text:p text:style-name="P17"><text:span text:style-name="T4">5. </text:span><text:span text:style-name="T12">Địa chỉ thường trú</text:span><text:span text:style-name="T11"> </text:span><text:span text:style-name="T13">(chỗ ở)</text:span><text:span text:style-name="T11">: ' . $residence . '</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="5" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">Đơn vị công tác: ' . $now_location . '</text:span><text:span text:style-name="T4"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="5" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">Công việc/Chức vụ hiện tại: ' . $now_job . '</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="5" office:value-type="string">
            <text:p text:style-name="P17"><text:span text:style-name="T4">6. </text:span><text:span text:style-name="T12">Các văn bằng đã có:</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P17"><text:span text:style-name="T3">a. </text:span><text:span text:style-name="T11">Bằng tốt nghiệp: <text:s text:c="3"/>THPT </text:span><text:span text:style-name="T11"><text:s text:c="5"/></text:span><draw:custom-shape text:anchor-type="char" draw:z-index="12" draw:name="Rectangle 5" draw:style-name="gr1" draw:text-style-name="P60" svg:width="0.1453in" svg:height="0.1504in" svg:x="1.9165in" svg:y="0.0492in">
                <text:p/>
                <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
                <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
                <draw:equation draw:name="f1" draw:formula="logheight/2"/>
                <draw:equation draw:name="f2" draw:formula="logheight"/>
                <draw:equation draw:name="f3" draw:formula="logwidth"/>
                </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T11"><text:s/></text:span><text:span text:style-name="T11"><text:s text:c="2"/>BTTH </text:span><draw:custom-shape text:anchor-type="char" draw:z-index="8" draw:name="Rectangle 3" draw:style-name="gr1" draw:text-style-name="P60" svg:width="0.1453in" svg:height="0.1504in" svg:x="2.7374in" svg:y="0.0555in">
                <text:p/>
                <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
                <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
                <draw:equation draw:name="f1" draw:formula="logheight/2"/>
                <draw:equation draw:name="f2" draw:formula="logheight"/>
                <draw:equation draw:name="f3" draw:formula="logwidth"/>
                </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T4"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">Năm TN: ……………………………………….</text:span><text:span text:style-name="T4"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">Nơi cấp bằng: …………………………………...</text:span><text:bookmark text:name="_GoBack"/><text:span text:style-name="T3"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P22"><text:span text:style-name="T1">, ký ngày: …..tháng………năm ………………..</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="5" office:value-type="string">
            <text:p text:style-name="P17"><text:span text:style-name="T3">Mã tổ hợp xét tuyển</text:span><text:span text:style-name="T11">: ………………………………………………..…………………………………</text:span><text:span text:style-name="T1"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="5" office:value-type="string">
            <text:p text:style-name="P18"><text:span text:style-name="T3">b.</text:span><text:span text:style-name="T11"> Các văn bằng chuyên môn (ghi cấp học cao nhất): THCN <text:s text:c="3"/></text:span><draw:custom-shape text:anchor-type="char" draw:z-index="9" draw:name="Rectangle 6" draw:style-name="gr1" draw:text-style-name="P60" svg:width="0.1453in" svg:height="0.1504in" svg:x="4.0638in" svg:y="0.0937in">
                <text:p/>
                <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
                <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
                <draw:equation draw:name="f1" draw:formula="logheight/2"/>
                <draw:equation draw:name="f2" draw:formula="logheight"/>
                <draw:equation draw:name="f3" draw:formula="logwidth"/>
                </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T11"><text:s text:c="2"/>Cao đẳng </text:span><draw:custom-shape text:anchor-type="char" draw:z-index="10" draw:name="Rectangle 10" draw:style-name="gr1" draw:text-style-name="P60" svg:width="0.1453in" svg:height="0.1504in" svg:x="4.9291in" svg:y="0.0839in">
                <text:p/>
                <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
                <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
                <draw:equation draw:name="f1" draw:formula="logheight/2"/>
                <draw:equation draw:name="f2" draw:formula="logheight"/>
                <draw:equation draw:name="f3" draw:formula="logwidth"/>
                </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T11"><text:s text:c="5"/>Đại học </text:span><draw:custom-shape text:anchor-type="char" draw:z-index="11" draw:name="Rectangle 11" draw:style-name="gr1" draw:text-style-name="P60" svg:width="0.1453in" svg:height="0.1504in" svg:x="5.698in" svg:y="0.0839in">
                <text:p/>
                <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
                <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
                <draw:equation draw:name="f1" draw:formula="logheight/2"/>
                <draw:equation draw:name="f2" draw:formula="logheight"/>
                <draw:equation draw:name="f3" draw:formula="logwidth"/>
                </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T3"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            </table:table-row>
            <table:table-row table:style-name="Table2.1">
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P23"><text:span text:style-name="T1">Ngành: ………………………………………….</text:span><text:span text:style-name="T3"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:table-cell table:style-name="Table2.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P23"><text:span text:style-name="T1">Năm tốt nghiệp: ………………………………...</text:span><text:span text:style-name="T3"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            </table:table-row>
        </table:table>
        <text:p text:style-name="P26"><text:span text:style-name="T4">7. Ngành đăng ký xét tuyển</text:span><text:span text:style-name="T7">(Ghi theo thứ tự ưu tiên)</text:span><text:span text:style-name="T9"/></text:p>
        <table:table table:name="Table3" table:style-name="Table3">
            <table:table-column table:style-name="Table3.A"/>
            <table:table-column table:style-name="Table3.B"/>
            <table:table-column table:style-name="Table3.C"/>
            <table:table-column table:style-name="Table3.D"/>
            <table:table-row table:style-name="Table3.1">
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P7"><text:span text:style-name="T14">TT</text:span><text:span text:style-name="T14"/></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P7"><text:span text:style-name="T14">Nguyện vọng</text:span><text:span text:style-name="T14"/></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P7"><text:span text:style-name="T14">Tên ngành</text:span><text:span text:style-name="T14"/></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P7"><text:span text:style-name="T14">Mã ngành</text:span><text:span text:style-name="T14"/></text:p>
            </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Table3.2">
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P27"><text:span text:style-name="T15">1</text:span><text:span text:style-name="T15"/></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P27"><text:span text:style-name="T15">NV1</text:span><text:span text:style-name="T15"/></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P27"><text:span text:style-name="T15">' . $major . '</text:span><text:span text:style-name="T15"/></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P13"/>
            </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Table3.3">
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P27"><text:span text:style-name="T15">2</text:span><text:span text:style-name="T15"/></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P27"><text:span text:style-name="T15">NV2</text:span><text:span text:style-name="T15"/></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P13"/>
            </table:table-cell>
            <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P13"/>
            </table:table-cell>
            </table:table-row>
        </table:table>
        <text:p text:style-name="P28"><draw:custom-shape text:anchor-type="char" draw:z-index="2" draw:name="Rectangle 7" draw:style-name="gr6" draw:text-style-name="P60" svg:width="0.2276in" svg:height="0.2059in" svg:x="6.0008in" svg:y="0.1772in">
            <text:p/>
            <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
            <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
            <draw:equation draw:name="f1" draw:formula="logheight/2"/>
            <draw:equation draw:name="f2" draw:formula="logheight"/>
            <draw:equation draw:name="f3" draw:formula="logwidth"/>
            </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T23">8</text:span><text:span text:style-name="T26">. Hồ sơ nộp kèm phiếu ĐKXT:</text:span><text:span text:style-name="T23"/></text:p>
        <text:p text:style-name="P30"><draw:custom-shape text:anchor-type="char" draw:z-index="6" draw:name="Rectangle 8" draw:style-name="gr3" draw:text-style-name="P60" svg:width="0.2276in" svg:height="0.2059in" svg:x="6.0008in" svg:y="0.1516in">
            <text:p/>
            <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
            <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
            <draw:equation draw:name="f1" draw:formula="logheight/2"/>
            <draw:equation draw:name="f2" draw:formula="logheight"/>
            <draw:equation draw:name="f3" draw:formula="logwidth"/>
            </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T22">- 01 </text:span>Phiếu đăng ký xét tuyển theo mẫu của Nhà trường<text:span text:style-name="T22"/></text:p>
        <text:p text:style-name="P30"><draw:custom-shape text:anchor-type="char" draw:z-index="7" draw:name="Rectangle 9" draw:style-name="gr2" draw:text-style-name="P60" svg:width="0.2276in" svg:height="0.2059in" svg:x="6.0008in" svg:y="0.1791in">
            <text:p/>
            <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
            <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
            <draw:equation draw:name="f1" draw:formula="logheight/2"/>
            <draw:equation draw:name="f2" draw:formula="logheight"/>
            <draw:equation draw:name="f3" draw:formula="logwidth"/>
            </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T22">- 01 </text:span>bản sao công chứng văn bằng cao nhất (THPT, trung cấp, cao đẳng, đại học) ;<text:span text:style-name="T22"/></text:p>
        <text:p text:style-name="P30"><draw:custom-shape text:anchor-type="char" draw:z-index="3" draw:name="Rectangle 4" draw:style-name="gr5" draw:text-style-name="P60" svg:width="0.2276in" svg:height="0.2059in" svg:x="6.0008in" svg:y="0.1756in">
            <text:p/>
            <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
            <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
            <draw:equation draw:name="f1" draw:formula="logheight/2"/>
            <draw:equation draw:name="f2" draw:formula="logheight"/>
            <draw:equation draw:name="f3" draw:formula="logwidth"/>
            </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T22">- 01 </text:span>bản sao công chứng bảng điểm hoặc Học bạ THPT<text:span text:style-name="T22">;</text:span> <text:span text:style-name="T22"/></text:p>
        <text:p text:style-name="P30"><draw:custom-shape text:anchor-type="char" draw:z-index="4" draw:name="Rectangle 2" draw:style-name="gr4" draw:text-style-name="P60" svg:width="0.2276in" svg:height="0.2059in" svg:x="6.0008in" svg:y="0.1689in">
            <text:p/>
            <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
            <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
            <draw:equation draw:name="f1" draw:formula="logheight/2"/>
            <draw:equation draw:name="f2" draw:formula="logheight"/>
            <draw:equation draw:name="f3" draw:formula="logwidth"/>
            </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T22">- 01 bản sao công chứng Chứng minh thư nhân dân hoặc Thẻ căn cước công dân</text:span><text:span text:style-name="T22"/></text:p>
        <text:p text:style-name="P30"><draw:custom-shape text:anchor-type="char" draw:z-index="5" draw:name="Rectangle 1" draw:style-name="gr3" draw:text-style-name="P60" svg:width="0.2276in" svg:height="0.2059in" svg:x="6.0008in" svg:y="0.1516in">
            <text:p/>
            <draw:enhanced-geometry draw:mirror-horizontal="false" draw:mirror-vertical="false" svg:viewBox="0 0 0 0" draw:text-areas="0 0 ?f3 ?f2" draw:type="ooxml-rect" draw:enhanced-path="M 0 0 L ?f3 0 ?f3 ?f2 0 ?f2 Z N">
            <draw:equation draw:name="f0" draw:formula="logwidth/2"/>
            <draw:equation draw:name="f1" draw:formula="logheight/2"/>
            <draw:equation draw:name="f2" draw:formula="logheight"/>
            <draw:equation draw:name="f3" draw:formula="logwidth"/>
            </draw:enhanced-geometry>
            </draw:custom-shape><text:span text:style-name="T22">- 01 phong bì đã dán tem và ghi rõ địa chỉ liên lạc của thí sinh </text:span><text:span text:style-name="T22"/></text:p>
        <text:p text:style-name="P30"><text:span text:style-name="T22">- 01 bản sao công chứng Giấy chứng nhận ưu tiên (nếu có)</text:span><text:span text:style-name="T22"/></text:p>
        <table:table table:name="Table4" table:style-name="Table4">
            <table:table-column table:style-name="Table4.A" table:number-columns-repeated="2"/>
            <table:table-row table:style-name="Table4.1">
            <table:table-cell table:style-name="Table4.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P19"><text:span text:style-name="T23">9. </text:span><text:span text:style-name="T26">Địa chỉ liên hệ của thí sinh</text:span><text:span text:style-name="T27">:</text:span><text:span text:style-name="T28"> ...</text:span><text:span text:style-name="T24"/></text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            </table:table-row>
            <table:table-row table:style-name="Table4.1">
            <table:table-cell table:style-name="Table4.A1" office:value-type="string">
            <text:p text:style-name="P19"><text:span text:style-name="T25">Điện thoại liên lạc: ' . $phone_number . '</text:span><text:span text:style-name="T23"/></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table4.A1" office:value-type="string">
            <text:p text:style-name="P19"><text:span text:style-name="T25">Email: ' . $email . '</text:span><text:span text:style-name="T23"/></text:p>
            </table:table-cell>
            </table:table-row>
        </table:table>
        <text:p text:style-name="P29"><text:span text:style-name="T25">Tôi xin cam đoan những nội dung ghi trong phiếu ĐKXT này là hoàn toàn là đúng sự thật. Nếu sai tôi xin chịu xử lý theo các quy định hiện hành của Bộ Giáo dục và Đào tạo.</text:span><text:span text:style-name="T25"/></text:p>
        <table:table table:name="Table5" table:style-name="Table5">
            <table:table-column table:style-name="Table5.A"/>
            <table:table-column table:style-name="Table5.B"/>
            <table:table-row table:style-name="Table5.1">
            <table:table-cell table:style-name="Table5.A1" office:value-type="string">
            <text:p text:style-name="P31"/>
            </table:table-cell>
            <table:table-cell table:style-name="Table5.A1" office:value-type="string">
            <text:p text:style-name="P24"><text:span text:style-name="T1">………., ngày … .tháng … năm ….</text:span><text:span text:style-name="T1"/></text:p>
            <text:p text:style-name="P24"><text:span text:style-name="T1">Người khai</text:span><text:span text:style-name="T1"/></text:p>
            <text:p text:style-name="P25"><text:span text:style-name="T1">(</text:span><text:span text:style-name="T7">Ký và ghi rõ họ tên</text:span><text:span text:style-name="T1">)</text:span><text:span text:style-name="T1"/></text:p>
            <text:p text:style-name="P32"/>
            <text:p text:style-name="P32"/>
            <text:p text:style-name="P20"><text:span text:style-name="T25">' . $fullname . '</text:span><text:span text:style-name="T25"/></text:p>
            </table:table-cell>
            </table:table-row>
        </table:table>
        ';

        switch ($school) {
            case 'TVU':
                $file_content = $file_content_tvu_header . $file_content_tvu_body_header . $file_content_tvu_body_footer . $file_content_tvu_footer;
                break;
            case 'UNETI':
                $file_content = $file_content_uneti_header . $file_content_uneti_body . $file_content_uneti_footer;
        }

        return $file_content;
    }
}
