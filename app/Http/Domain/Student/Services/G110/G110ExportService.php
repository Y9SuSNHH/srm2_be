<?php

namespace App\Http\Domain\Student\Services\G110;

use App\Helpers\CsvParser;
use App\Http\Domain\Student\Requests\Student\SearchRequest;
use App\Http\Domain\Student\Repositories\Student\StudentRepositoryInterface;
use App\Http\Enum\ReceivablePurpose;
use App\Http\Domain\Reports\Services\XmlGenerator;
use App\Http\Enum\ProfileStatus;
use App\Http\Enum\TuitionFee;
use App\Http\Support\Helper;
use Illuminate\Validation\ValidationException;
use ReflectionException;
use Throwable;

class G110ExportService
{
    /**
     * student_repository
     *
     * @var mixed
     */
    private $student_repository;

    public function __construct(StudentRepositoryInterface $student_repository)
    {
        $this->student_repository = $student_repository;
    }

    public function getData($data)
    {
        [$students, $first_day_of_school, $qlht, $area] = $this->student_repository->getDataG110($data);
    //    dd($students);
        $rows = [];
        $password = 'tvu@'.date('Y',time());
            if(!$students->isEmpty())
            {
                $thuc_thu = $this->student_repository->getThucThu($students);
                // dd($thuc_thu);
                foreach ($students as $student)
                {
                    $phai_thu_hoc_phi =  0;
                    $phai_thu_lpxt = 0;
                    if(isset($student->studentProfile->studentReceivables))
                    {
                        $phai_thu_list = $student->studentProfile->studentReceivables;
                        foreach($phai_thu_list as $phai_thu)
                        {
                            if($phai_thu->purpose == ReceivablePurpose::ADMISSION_FEE)
                            {
                                $phai_thu_lpxt = $phai_thu->receivable;
                            } else if ($phai_thu->purpose == ReceivablePurpose::TUITION_FEE) {
                                $phai_thu_hoc_phi = $phai_thu->receivable;
                            }
                        }
                    }
                    // dd($student->studentClassrooms);
                    // if($student->studentProfile->profile_code == 'TVU02055')
                        // dd($thuc_thu['TVU02055']);
                    $thuc_thu_hoc_phi = 0;
                    $thuc_thu_lpxt = 0;
                    $profile_code = $student->studentProfile->profile_code;
                    // dd($profile_code);
                    foreach($thuc_thu as $key => $thucthu)
                    {
                        // dd($thucthu);
                        if($profile_code == $key)
                        {
                            $thuc_thu_hoc_phi = $thucthu['hoc_phi'];
                            $thuc_thu_lpxt = $thucthu['lpxt'];
                            break;
                        }
                    }

                    $student_profile = $student->studentProfile;
                    $profile = $student_profile->profile;
                    $curriculum_vitae=json_decode($profile->curriculum_vitae);
                    // $key_profile_status = ProfileStatus::search($student->profile_status);
                    
                    $rows[] = [
                        // $student->id,
                        $student->getTvts->fullname ?? '', // TVTS
                        $student->studentClassrooms->first()->getClassroom?->area?->name ?? '', // khu vuc
                        $student->studentProfile->profile_code ?? '', // ma ho so
                        $student->student_code, // ma sinh vien
                        $profile->firstname ?? '', // ho va dem
                        $profile->lastname ?? '', // ten
                        $profile->birthday ? date('d-m-Y',strtotime($profile->birthday)) : $profile->borned_year, // ngay sinh
                        $profile->gender == 0 ? 'Nam' : 'Nữ', // Giới tính
                        $profile->borned_place, // Nơi sinh
                        $student->studentClassrooms->first()->getClassroom?->code ?? '', // Tên lớp
                        $student->studentClassrooms->first()->getClassroom?->major?->name, // Ngành đăng ký
                        $curriculum_vitae->certificate ?? '', // certificate
                        $curriculum_vitae->majored_in ?? '', // majoredIn
                        $profile->main_residence?? '', // Địa chỉ liên hệ
                        substr($profile->phone_number,0,1) == '0'? '\''.$profile->phone_number : '\'0'.$profile->phone_number, // Số điện thoại
                        $profile->email ?? '', // Email cá nhân
                        $student->account ?? '', // Tài khoản học tập
                        $student->email ? $student->email : '', //email học tập
                        $student->email ? $password : '',
                        number_format($phai_thu_lpxt,0,'.',','), // Phải thu LPXT
                        number_format($phai_thu_hoc_phi,0,'.',','), // Phải thu học phí kỳ 1
                        number_format($thuc_thu_lpxt,0,'.',','), // Thực thu LPXT
                        number_format($thuc_thu_hoc_phi,0,'.',','), // Thực thu học phí kỳ 1 (không kèm Lệ phí xét tuyển)
                        number_format($thuc_thu_hoc_phi - $phai_thu_hoc_phi,0,'.',','), // chenh lech
                        ProfileStatus::fromOptional($student->profile_status)->getLang() ?? '', // 'trang thai ho so giao vu'
                        $student->note ?? '', // ghi chu
                    ];
                }
            }
        // dd($rows);
        return [$rows, $first_day_of_school, $qlht, $area];
    }

    public function generateFile($request) 
    {
        [$g110_data, $first_day_of_school, $staff, $area] = $this->getData($request);
        $file_content = file_get_contents(storage_path(sprintf('app/template/%s','template_g110.fods')));
        
        $sheet_heade         =  $this->sheetHead($first_day_of_school, $staff, $area); 
        $sheet_table_content = $this->sheetContent($g110_data);
        $sheet_close         = $this->sheetClose();
        
        $sheet_content = $file_content . $sheet_heade . $sheet_table_content . $sheet_close;
        // dd($sheet_content);
        return $sheet_content;

    }

    public function sheetHead($first_day_of_school, $staff, $area) {
        $file_content = '<table:table-row table:style-name="ro2">
            <table:table-cell table:style-name="ce3" table:number-columns-repeated="5"/>
            <table:table-cell table:style-name="ce9" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="1">
            <text:p>Ngày khai giảng:'.$first_day_of_school.'</text:p>
            </table:table-cell>
            <table:covered-table-cell table:number-columns-repeated="2" table:style-name="ce3"/>
            <table:table-cell/>
            <table:table-cell table:style-name="ce9" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>QLHT:'.$staff.'</text:p>
            </table:table-cell>
            <table:covered-table-cell table:style-name="ce3"/>
            <table:table-cell table:style-name="ce3" table:number-columns-repeated="16"/>
            <table:table-cell table:number-columns-repeated="16357"/>
        </table:table-row>
        <table:table-row table:style-name="ro2">
            <table:table-cell table:style-name="ce3" table:number-columns-repeated="5"/>
            <table:table-cell table:style-name="ce9" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="1">
            <text:p>Ngày báo cáo:'.date('d/m/Y').'</text:p>
            </table:table-cell>
            <table:covered-table-cell table:number-columns-repeated="2" table:style-name="ce3"/>
            <table:table-cell/>
            <table:table-cell table:style-name="ce9" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Khu vực:'.$area.'</text:p>
            </table:table-cell>
            <table:covered-table-cell table:style-name="ce3"/>
            <table:table-cell table:style-name="ce3" table:number-columns-repeated="16"/>
            <table:table-cell table:number-columns-repeated="16357"/>
        </table:table-row>
        <table:table-row table:style-name="ro3">
            <table:table-cell table:style-name="ce3" table:number-columns-repeated="27"/>
            <table:table-cell table:number-columns-repeated="16357"/>
        </table:table-row>
        <table:table-row table:style-name="ro4">
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>STT</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>TVTS</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Khu vực</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Mã hồ sơ</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Mã Sinh viên</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Họ và đệm</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Tên</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Ngày sinh</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Giới tính</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Nơi sinh</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Tên lớp</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Ngành đăng ký</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce9" office:value-type="string" calcext:value-type="string">
      <text:p>Bằng tốt nghiệp sử dụng đăng ký xét tuyển</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce9" office:value-type="string" calcext:value-type="string">
      <text:p>Ngành đã tốt nghiệp</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce9" office:value-type="string" calcext:value-type="string">
      <text:p>Địa chỉ liên hệ</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Số điện thoại</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Email cá nhân</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce10" office:value-type="string" calcext:value-type="string">
      <text:p>Tài khoản học tập</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce10" office:value-type="string" calcext:value-type="string">
      <text:p>Tài khoản email học tập</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce10" office:value-type="string" calcext:value-type="string">
      <text:p>Mật khẩu</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Phải thu LPXT</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Phải thu học phí kỳ 1</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Thực thu LPXT</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Thực thu học phí kỳ 1 (không kèm Lệ phí xét tuyển)</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Chênh lệch = Thực thu - Phải thu</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Trạng thái hồ sơ giáo vụ</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string">
      <text:p>Ghi chú</text:p>
     </table:table-cell>
    </table:table-row>';

        return $file_content;
    }

    public function sheetContent(array $content) {
        $table_content = '';
        $i = 1;
        foreach($content as $row)
        {
            $table_content .= '<table:table-row table:style-name="ro5">'.Helper::generateXMLElement('ce7','string',$i);

            foreach($row as $content)
            {
                $table_content .= Helper::generateXMLElement('ce7','string',$content != null ? $content : '');
            }
            $i++;
            $table_content .= '</table:table-row>';
        }
        $table_content .= '</table:table>';
        return $table_content;
    }

    public function sheetClose()
    {
        return '
                        <table:named-expressions/>
                    </office:spreadsheet>
                </office:body>
            </office:document>';
    }

    // public function getThucThuSV($students) {

    //     $result = $this->student_repository->getThucThu($students);
    //     // dd($result);
    //     $result->transform(function ($sv) {

    //         $thuc_thu[$sv->ma_ho_so]['hoc_phi'] = 0;
    //         $thuc_thu[$sv->ma_ho_so]['lpxt'] = 0;

    //         foreach($sv->amountsReceived as $thucthu)
    //         {
    //             // dd($thuc_thu);
    //             if($thucthu->muc_dich_thu == TuitionFee::FREE)
    //             {
    //                 $thuc_thu[$sv->ma_ho_so]['hoc_phi'] += $thucthu->thuc_nop;
    //             } else if ($thucthu->muc_dich_thu == TuitionFee::ADMISSION_FEE) {
    //                 $thuc_thu[$sv->ma_ho_so]['lpxt'] = $thucthu->thuc_nop;
    //             }
    //         }
    //         return $thuc_thu;
    //     })->toArray();
    //     // dd($result);
    //     return $result;
    // } 
}
