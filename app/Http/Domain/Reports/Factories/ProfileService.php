<?php


namespace App\Http\Domain\Reports\Factories;


use App\Helpers\CsvParser;
use App\Http\Domain\Reports\Requests\F111\UploadRequest;
use App\Http\Domain\Reports\Factories\ProfileServiceInterface;
use App\Http\Domain\Reports\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Reports\Repositories\StudentProfile\StudentProfileRepositoryInterface;
use Illuminate\Support\Facades\Log;
use App\Http\Enum\IdentificationDiv;
use App\Http\Enum\StudentStatus;
use App\Http\Domain\AcademicAffairsOfficer\Services\ClassService;
use App\Http\Domain\Api\Services\StaffService;
use App\Http\Domain\Reports\Repositories\Profile\ProfileRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Services\AreaService;
use App\Http\Domain\TrainingProgramme\Services\EnrollmentObjectService;
use App\Http\Domain\TrainingProgramme\Services\EnrollmentWaveService;
use App\Http\Domain\TrainingProgramme\Services\MajorService;
use App\Http\Enum\ObjectClassification;
use App\Http\Enum\ProfileStatus;
use App\Http\Enum\Subjects;
use Carbon\Carbon;

class ProfileService implements ProfileServiceInterface
{
    private $errors;
    private $preview;
    private $profiles;
    private $student_profiles;
    private $students;
    private $student_classrooms;
    private $profile_repository;
    private $student_profile_repository;
    private $student_repository;

    public function __construct(StudentProfileRepositoryInterface $student_profile_repository, StudentRepositoryInterface $student_repository, ProfileRepositoryInterface $profile_repository)
    {
        $this->errors = [];
        $this->preview = [];
        $this->profiles = [];
        $this->student_profiles = [];
        $this->students = [];
        $this->student_classrooms = [];
        $this->student_profile_repository = $student_profile_repository;
        $this->student_repository = $student_repository;
        $this->profile_repository = $profile_repository;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getPreview(): array
    {
        return $this->preview;
    }

    public function getProfile(): array
    {
        return $this->profiles;
    }

    public function getStudentProfile(): array
    {
        return $this->student_profiles;
    }
    public function getStudent(): array
    {
        return $this->students;
    }

    public function getStudentClassroom(): array
    {
        return $this->student_classrooms;
    }

    public function getLabels(): array
    {
        return [
            'A' => 'STT',
            'B' => 'TVTS',
            'C' => 'Tên TS8',
            'D' => 'Đợt khai giảng',
            'E' => 'Mã hồ sơ cũ',
            'F' => 'Mã hồ sơ',
            'G' => 'Gộp cũ',
            'H' => 'Gộp chuyển đến',
            'I' => 'Khu vực',
            'J' => 'Họ & tên',
            'K' => 'Họ đệm',
            'L' => 'Tên',
            'M' => 'Giới tính',
            'N' => 'Ngày sinh',
            'O' => 'Nơi sinh',
            'P' => 'Dân tộc',
            'Q' => 'Tôn giáo',
            'R' => 'Số CMND/CCCD',
            'S' => 'Ngày cấp',
            'T' => 'Nơi cấp',
            'U' => 'Hộ khẩu thường trú',
            'V' => 'Địa chỉ liên hệ',
            'W' => 'Điện thoại',
            'X' => 'Email cá nhân',
            'Y' => 'Đối tượng (Đã tốt nghiệp)',
            'Z' => 'Ngành Tốt Nghiệp',
            'AA' => 'Năm Tốt Nghiệp',
            'AB' => 'Nơi cấp bằng',
            'AC' => 'Trường học lớp 12 bậc THPT',
            'AD' => 'Quận/huyện của Trường học lớp 12',
            'AE' => 'Tỉnh/TP của Trường học lớp 12',
            'AF' => 'Công việc hiện nay',
            'AG' => 'Cơ quan công tác',
            'AH' => 'Họ và tên Người thân(đại diện) 1',
            'AI' => 'Mối quan hệ',
            'AJ' => 'Nghề nghiệp',
            'AK' => 'Điện thoại',
            'AL' => 'Địa chỉ',
            'AM' => 'Họ và tên Người thân(đại diện) 2',
            'AN' => 'Mối quan hệ',
            'AO' => 'Nghề nghiệp',
            'AP' => 'Điện thoại',
            'AQ' => 'Địa chỉ',
            'AR' => 'Khóa',
            'AS' => 'Ngày đăng ký',
            'AT' => 'Mã viết tắt ngành',
            'AU' => 'Mã viết tắt đối tượng',
            'AV' => 'Mã viết tắt phân loại đối tượng',
            'AW' => 'Mã lớp',
            'AX' => 'Mã lớp trên QĐTT (file excel)',
            'AY' => 'Trạng thái hồ sơ Giáo vụ',
            'AZ' => 'Ghi chú',
            'BA' => 'Level',
            'BB' => 'Mã tổ hợp xét tuyển',
            'BC' => 'Tên tổ hợp',
            'BD' => 'Đầu điểm 1',
            'BE' => 'Đầu điểm 2',
            'BF' => 'Đầu điểm 3',
            'BG' => 'Điểm xét tuyển',
            'BH' => 'Xếp loại tốt nghiệp',
            'BI' => 'Ngày gửi GV F111',
            'BJ' => 'Nhóm NNA',
        ];
    }

    public function processUploadFile(UploadRequest $request): bool
    {
        try {
            $profile_codes = [];
            $classrooms = [];
            $areas = [];
            $enrollment_objects = [];
            $majors = [];
            $enrollment_waves = [];
            $staffs = [];
            $profiles = [];
            $csv = new CsvParser($request->file, self::getLabels());
            $get_items = new CsvParser($request->file, self::getLabels());
            $get_items->each(function ($row, $index) use (&$profile_codes, &$classrooms, &$areas, &$enrollment_objects, &$enrollment_waves, &$majors, &$staffs, &$profiles) {
                $row = array_map('trim', $row);
                if (empty(array_filter($row))) {
                    return false;
                }
                array_push($profile_codes, $row['F']);

                array_push($classrooms, $row['AW']);

                array_push($areas, $row['I']);

                array_push($enrollment_objects, $row['AU']);

                array_push($majors, $row['AT']);

                array_push($enrollment_waves, get_carbon_vn($row['D']));

                array_push($staffs, mb_strtolower($row['B']));

                array_push($profiles, mb_strtolower($row['R']));

                return true;
            });
            
            $student_profiles = $this->student_profile_repository->findExistedStudents($profile_codes)->keyBy('profile_code')->toArray();
            $list_profiles = $this->profile_repository->findExistedProfiles($profiles)->keyBy('identification')->toArray();
            $list_classrooms = app()->service(ClassService::class)->findExistedClassrooms($classrooms)->keyBy('code')->toArray();
            $list_areas = app()->service(AreaService::class)->findExistedAreas($areas)->keyBy('code')->toArray();
            $list_enrollment_objects = app()->service(EnrollmentObjectService::class)->findExistedEnrollmentObjects($enrollment_objects)->keyBy('shortcode')->toArray();
            $list_majors = app()->service(MajorService::class)->findExistedMajors($majors)->keyBy('shortcode')->toArray();
            $list_enrollment_waves = app()->service(EnrollmentWaveService::class)->findExistedEnrollmentWaves($enrollment_waves)->keyBy('first_day_of_school')->toArray();
            $list_staffs = app()->service(StaffService::class)->findExistedStaffs($staffs)
                            ->keyBy(function($item) {
                                $user = $item->user;
                                if($user){
                                    return $user->username;
                                }
                            })->toArray();
            $csv->each(function ($row, $index, $is_not_matches) use ($student_profiles, $list_classrooms, $list_areas, $list_enrollment_objects, $list_majors, $list_enrollment_waves, $list_staffs, $list_profiles, $profiles, $profile_codes) {

                $row = array_map('trim', $row);
                if (empty(array_filter($row))) {
                    return false;
                }
                $error = [];
                if ($is_not_matches) {
                    $error[$index] = ['Số lượng cột không hợp lệ'];
                    return;
                }

                [
                    $error,
                    $birthday,
                    $grant_date,
                ] = $this->validateRequired($row);

                $student_profile = $student_profiles[$row['F']] ?? null;
                $count_student_profiles = array_count_values($profile_codes);
                if ($student_profile) {
                    $error[] = 'Mã hồ sơ đã tồn tại';
                } elseif ($count_student_profiles[$row['F']] > 1) {
                    $error[] = 'Mã hồ sơ trong file bị trùng lặp';
                }
                
                $student_identification = $list_profiles[$row['R']] ?? null;
                $count_student_identification = array_count_values($profiles);
                if ($student_identification) {
                    $error[] = 'Số CMND/CCCD đã tồn tại';
                } elseif ($count_student_identification[$row['R']] > 1) {
                    $error[] = 'Số CMND/CCCD trong file bị trùng lặp';
                }

                $staff = $list_staffs[mb_strtolower($row['B'])] ?? null;
                if (!$staff) {
                    $error[] = 'TVTS không hợp lệ';
                }

                $classroom = $list_classrooms[$row['AW']] ?? null;
                if (!$classroom) {
                    $error[] = 'Mã lớp không hợp lệ';
                } 
                // else {
                //     $area = $classroom['area']['code'] ?? '';
                //     if ($area !== $row['I']) {
                //         $error[] = 'Khu vực không hợp lệ';
                //     }

                //     $major = $classroom['major']['shortcode'] ?? '';
                //     if ($major !== $row['AT']) {
                //         $error[] = 'Mã viết tắt ngành không hợp lệ';
                //     }

                //     $enrollment_object_shortcode = $classroom['enrollment_object']['shortcode'] ?? '';
                //     if ($enrollment_object_shortcode !== $row['AU']) {
                //         $error[] = 'Mã viết tắt đối tượng không hợp lệ';
                //     }
                    
                //     $enrollment_object_classification = $classroom['enrollment_object']['classification'] ?? '';
                //     $classification = ObjectClassification::findValueF111(trim(mb_strtolower($row['AV'])));
                //     if(empty($row['AV'])){
                //         if(!empty($enrollment_object_classification)){
                //             $error[] = 'Mã viết tắt PLĐT không hợp lệ';
                //         }
                //     } else {
                //         if(empty($classification)){
                //             $error[] = 'Mã viết tắt PLĐT không hợp lệ';
                //         } elseif (!empty($enrollment_object_classification) && !empty($classification) && $classification !== $enrollment_object_classification) {
                //             $error[] = 'Mã viết tắt PLĐT không hợp lệ';
                //         } elseif (empty($enrollment_object_classification) && !empty($classification)) {
                //             $error[] = 'Mã viết tắt PLĐT không hợp lệ';
                //         }
                //     }
                    
                //     $first_day_of_school = $classroom['enrollment_wave']['first_day_of_school'] ?? null;
                //     if ($first_day_of_school === null || !Carbon::parse($first_day_of_school)->ne($row['D'])) {
                //         $error[] = 'Đợt khai giảng không hợp lệ';
                //     }
                // }

                if (!empty($error)) {
                    $this->errors[$index] = $error;
                    return;
                }

                $firstname = $row['K'];
                $lastname = $row['L'];
                $gender = trim(mb_strtolower($row['M'], 'UTF-8')) == 'nam' ? 0 : 1;
                $identification = $row['R'];
                if (!$identification) {
                    $identification = dechex(time() + $index);
                }
                if (is_numeric($row['R']) && preg_match('/^\d{9}(\d{3})?$/', $row['R'])) {
                    $identification_div = IdentificationDiv::AVAILABLE;
                } else {
                    $identification_div = IdentificationDiv::UNAVAILABLE;
                }
                $grant_place = $row['T'];
                $main_residence = $row['U'];
                $address_units = explode(',', $main_residence);
                $city = array_pop($address_units);
                $district = array_pop($address_units);
                $ward = array_pop($address_units);
                $village = implode(',', $address_units);
                $borned_year = date('Y', strtotime($birthday));
                $borned_place = $row['O'];
                $phone_number = $row['W'];
                $nation = $row['P'];
                $religion = $row['Q'];
                $email = $row['X'];
                $address = $row['V'];
                $curriculum_vitae = [
                    'job' => $row['AF'], // Công việc hiện nay
                    'ward' => $ward,
                    'village' => $village,
                    'district' => $district,
                    'city' => $city,
                    'majored_in' => $row['Z'], // Ngành đã học
                    'certificate' => $row['Y'], // Bằng tốt nghiệp
                    'degree_place' => $row['AB'], // Nơi cấp bằng
                    'place_of_issue' => null,
                    'working_agency' => $row['AG'], //Nơi công tác
                    'graduation_year' => $row['AA'], //Năm tốt nghiệp
                    'high_school_name' => $row['AC'], // Trường học
                    'high_school_district' => $row['AD'], // Quận/huyện
                    'high_school_city' => $row['AE'], // Tỉnh/TP
                    'deputy_1' => $row['AH'], // Người đại diện 1
                    'deputy_relation_1' => $row['AI'], // Quan hệ
                    'deputy_job_1' => $row['AJ'], // Nghề nghiệp
                    'deputy_phone_1' => $row['AK'], // Điện thoại
                    'deputy_address_1' => $row['AL'], // Địa chỉ
                    'deputy_2' => $row['AM'], //Người đại diện 2
                    'deputy_relation_2' => $row['AN'], //Quan hệ
                    'deputy_job_2' => $row['AO'], //Nghề nghiệp
                    'deputy_phone_2' => $row['AP'], //Điện thoại
                    'deputy_address_2' => $row['AQ'], //Địa chỉ
                ];
                $now = Carbon::now();
                $this->profiles[] = [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'gender' => $gender,
                    'identification' => $identification,
                    'identification_div' => $identification_div,
                    'grant_date' => $grant_date->toDateString(),
                    'grant_place' => $grant_place,
                    'main_residence' => $main_residence,
                    'birthday' => $birthday->toDateString(),
                    'borned_year' => $borned_year,
                    'borned_place' => $borned_place,
                    'phone_number' => $phone_number,
                    'nation' => $nation,
                    'religion' => $religion,
                    'email' => $email,
                    'address' => $address,
                    'curriculum_vitae' => $curriculum_vitae,
                    'created_by' => auth()->getId(),
                    'created_at' => $now,
                    'updated_by' => auth()->getId(),
                    'updated_at' => $now,
                ];
                $documents = [
                    'registration_form' => null, // Phiếu đăng ký theo mẫu
                    'application_form' => null, // Phiếu dự tuyển
                    'graduate_degree' => null, // Bằng tốt nghiệp
                    'transcript' => null,  // Bảng điểm
                    'card_image' => null, // Ảnh 3x4
                    'profile_receive_area' => null, // Khu vực tiếp nhận hồ sơ
                    'university' => null, // Trường
                    'gop_ban_dau' => null, // Gộp ban đầu
                    'gop_chuyen_den' => null, // Gộp chuyển đến
                    'gop_dang_ki' => null, // Gộp đăng kí
                    'gop_khai_giang' => null, // Gộp khai giảng
                    'tuan' => null, // Tuần
                    'receive_date' => null, // Ngày nhận hồ sơ từ TVTS
                    'delivery_date_tvu' => null, // Ngày bàn giao TVU Cứng
                    'delivery_date_tvu_scan' => null, // Ngày bàn giao TVU scan
                    'report_error' => null, // Phản hồi hồ sơ lỗi giáo vụ
                    'decision_no' => null, // Số Quyết định
                    'decision_date' => null, // Ngày ký QĐTT
                    'decision_return_date' => null, // Ngày GV trả QĐ cho TVTS
                    'student_card_received_date' => null, // Ngày TVU trả thẻ SV
                    'delivery_student_profile_date' => null, // Ngày giáo vụ BGHS trường
                    'subject' => $row['BC'], // Tổ hợp xét tuyển
                    'subject_code' => Subjects::getValueByKey($row['BB']), // Mã tổ hợp xét tuyển
                    'grade_1' => $row['BD'], // Điểm tổ hợp 1
                    'grade_2' => $row['BE'], // Điểm tổ hợp 2
                    'grade_3' => $row['BF'], // Điểm tổ hợp 3
                    'grade_subject' => $row['BG'], // Điểm xét tuyển
                    'grade_avg_subject' => null, // Điểm TB TC,CĐ,ĐH
                    'rank_subject' => $row['BH'], // Xếp loại tốt nghiệp
                    'profile_status_tkts' => null, // Trạng thái HS TKTS
                    'date_delivery_document_admission' => null, // Ngày bàn giao hồ sơ xét tuyển
                ];
                
                $first_day_of_school = get_carbon_vn($row['D'])->toDateTimeString();
                $enrollment_wave = $list_enrollment_waves[$first_day_of_school] ?? null;
                $this->student_profiles[] = [
                    'school_id' => school()->getId(),
                    'profile_code' => $row['F'],
                    'staff_id' => $staff['id'] ?? null,
                    'is_ts8' => false,
                    'area_id' => $classroom['area']['id'] ?? null,
                    'major_id' => $classroom['major']['id'] ?? null,
                    'enrollment_object_id' => $classroom['enrollment_object']['id'] ?? null,
                    'enrollment_wave_id' => $classroom['enrollment_wave']['id'] ?? null,
                    'classroom_id' => $classroom['id'] ?? null,
                    'level' => null,
                    'documents' => $documents,
                    'created_by' => auth()->getId(),
                    'created_at' => $now,
                    'updated_by' => auth()->getId(),
                    'updated_at' => $now,
                    'identification' => $identification,
                    'identification_div' => $identification_div,
                ];
                
                $account = school()->getCode() === 'UNETI' ? null : $this->createAccount($firstname, $lastname, $row['N']);
                // if($account){
                //     $student_email = $account . '@' . mb_strtolower(school()->getCode()) . '-onschool.edu.vn';
                // }
                $profile_status = 0;
                // foreach (ProfileStatus::values() as $key => $value) {
                //     if ($key == $row['AY']) {
                //         $profile_status = $value;
                //     }
                // }
                $this->students[] = [
                    'school_id' => school()->getId(),
                    'account' => $account ?? null,
                    'email' => $account ? "{$account}@onschool.asia" : null,
                    'profile_status' => $profile_status,
                    'student_status' => StudentStatus::DANG_HOC_CHUA_HS,
                    'note' => $row['AZ'],
                    'created_by' => auth()->getId(),
                    'created_at' => $now,
                    'updated_by' => auth()->getId(),
                    'updated_at' => $now,
                    'profile_code' => $row['F'],
                ];
                
                $this->student_classrooms[] = [
                    'classroom_id' => $classroom['id'] ?? null,
                    'began_at' => $now,
                    'began_date' => $now,
                    'reference_type' => 0,
                    'created_by' => auth()->getId(),
                    'created_at' => $now,
                    'updated_by' => auth()->getId(),
                    'updated_at' => $now,
                    'profile_code' => $row['F'],
                ];
                $this->preview[] = $row;
            });

            return true;
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            return false;
        }
    }

    public function validateRequired(array $row): array
    {
        $errors = [];
        $birthday = get_carbon_vn($row['N']);
        $grant_date = get_carbon_vn($row['S']);
        $first_day_of_school = get_carbon_vn($row['D']);
        if (empty($row['B'])) {
            $errors[] = __('validation.required', ['attribute' => 'Cán bộ TVTS']);
        }

        // if (empty($row['D'])) {
        //     $errors[] = __('validation.required', ['attribute' => 'Đợt khai giảng']);
        // } elseif (!$first_day_of_school) {
        //     $errors[] = __('validation.date_format', ['attribute' => 'Ngày khai giảng', 'format' => 'd/m/Y']);
        // }

        if (!empty($row['D']) && !$first_day_of_school) {
            $errors[] = __('validation.date_format', ['attribute' => 'Ngày khai giảng', 'format' => 'd/m/Y']);
        }

        if (empty($row['F'])) {
            $errors[] = __('validation.required', ['attribute' => 'Mã hồ sơ']);
        }

        // if (empty($row['I'])) {
        //     $errors[] = __('validation.required', ['attribute' => 'Khu vực']);
        // }

        if (empty($row['K'])) {
            $errors[] = __('validation.required', ['attribute' => 'Họ đệm']);
        }

        if (empty($row['L'])) {
            $errors[] = __('validation.required', ['attribute' => 'Tên']);
        }

        if (empty($row['M'])) {
            $errors[] = __('validation.required', ['attribute' => 'Giới tính']);
        }
        
        if (empty($row['N'])) {
            $errors[] = __('validation.required', ['attribute' => 'Ngày sinh']);
        } elseif (!$birthday) {
            $errors[] = __('validation.date_format', ['attribute' => 'Ngày sinh', 'format' => 'd/m/Y']);
        } elseif ((int)substr($row['N'], -4) > (Carbon::now()->year - 18)) {
            $errors[] = 'Sinh viên chưa đủ độ tuổi';
        }

        if (empty($row['O'])) {
            $errors[] = __('validation.required', ['attribute' => 'Nơi sinh']);
        }

        if (empty($row['P'])) {
            $errors[] = __('validation.required', ['attribute' => 'Dân tộc']);
        }

        if (empty($row['Q'])) {
            $errors[] = __('validation.required', ['attribute' => 'Tôn giáo']);
        }

        if (empty($row['R'])) {
            $errors[] = __('validation.required', ['attribute' => 'Số CMND/CCCD']);
        } elseif (!preg_match('/^[0-9]\d*$/', $row['R'])) {
            $errors[] = __('validation.numeric', ['attribute' => 'Số CMND/CCCD']);
        } elseif (!preg_match('/^[0-9]{9}$|^[0-9]{12}$/', $row['R'])) {
            $errors[] = __('validation.in', ['attribute' => 'Số CMND/CCCD']);
        }

        if (empty($row['S'])) {
            $errors[] = __('validation.required', ['attribute' => 'Ngày cấp']);
        } elseif (!$grant_date) {
            $errors[] = __('validation.date_format', ['attribute' => 'Ngày cấp', 'format' => 'd/m/Y']);
        }

        if (empty($row['T'])) {
            $errors[] = __('validation.required', ['attribute' => 'Nơi cấp']);
        }

        if (empty($row['U'])) {
            $errors[] = __('validation.required', ['attribute' => 'Hộ khẩu thường trú']);
        }

        if (empty($row['V'])) {
            $errors[] = __('validation.required', ['attribute' => 'Địa chỉ liên hệ']);
        }

        if (empty($row['W'])) {
            $errors[] = __('validation.required', ['attribute' => 'Điện thoại']);
        } elseif (!preg_match('/^(\+84|0)\d{9,10}$/', $row['W'])) {
            $errors[] = 'Trường Điện thoại không hợp lệ';
        }

        if (empty($row['X'])) {
            $errors[] = __('validation.required', ['attribute' => 'Email cá nhân']);
        } elseif ($row['X'] && !filter_var($row['X'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = __('validation.email', ['attribute' => 'Email cá nhân']);
        }

        if (empty($row['Y'])) {
            $errors[] = __('validation.required', ['attribute' => 'Đối tượng (Đã tốt nghiệp)']);
        }

        // if (empty($row['Z'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Ngành tốt nghiệp']);
        // }

        // if (empty($row['AA'])) {
        //     $errors[] = __('validation.required', ['attribute' => 'Năm tốt nghiệp']);
        // } elseif (!preg_match('/^[1-9]\d*$/', $row['AA'])) {
        //     $errors[] = __('validation.numeric', ['attribute' => 'Năm tốt nghiệp']);
        // }

        // if (empty($row['AB'])) {
        //     $errors[] = __('validation.required', ['attribute' => 'Nơi cấp bằng']);
        // }

        if (empty($row['AC'])) {
            $errors[] = __('validation.required', ['attribute' => 'Trường học lớp 12 bậc THPT']);
        }

        if (empty($row['AD'])) {
            $errors[] = __('validation.required', ['attribute' => 'Quận/huyện của Trường học lớp 12']);
        }

        if (empty($row['AE'])) {
            $errors[] = __('validation.required', ['attribute' => 'Tỉnh/TP của Trường học lớp 12']);
        }

        // if (empty($row['AF'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Công việc hiện nay']);
        // }

        // if (empty($row['AG'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Cơ quan công tác']);
        // }

        // if (empty($row['AH'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Họ và tên Người thân(đại diện) 1']);
        // }

        // if (empty($row['AI'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Mối quan hệ']);
        // }

        // if (empty($row['AJ'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Nghề nghiệp']);
        // }

        // if (empty($row['AK'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Điện thoại']);
        // }

        if (!empty($row['AK'])) {
            if (!preg_match('/^(\+84|0)\d{9,10}$/', $row['AK'])) {
                $errors[] = 'Trường Điện thoại của người thân 1 không hợp lệ';
            }
        }

        // if (empty($row['AL'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Địa chỉ']);
        // }

        // if (empty($row['AM'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Họ và tên Người thân(đại diện) 2']);
        // }

        // if (empty($row['AN'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Mối quan hệ']);
        // }

        // if (empty($row['AO'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Nghề nghiệp']);
        // }

        // if (empty($row['AP'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Điện thoại']);
        // }

        if (!empty($row['AP'])) {
            if (!preg_match('/^(\+84|0)\d{9,10}$/', $row['AP'])) {
                $errors[] = 'Trường Điện thoại của người thân 2 không hợp lệ';
            }
        }

        // if (empty($row['AQ'])) {
        //   $errors[] = __('validation.required', ['attribute' => 'Địa chỉ']);
        // }

        // if (empty($row['AT'])) {
        //     $errors[] = __('validation.required', ['attribute' => 'Mã viết tắt ngành']);
        // }

        // if (empty($row['AU'])) {
        //     $errors[] = __('validation.required', ['attribute' => 'Mã đối tượng']);
        // }

        if (empty($row['AW'])) {
            $errors[] = __('validation.required', ['attribute' => 'Mã lớp']);
        }

        // if ($row['BD']) {
        //     $errors[] = __('validation.numeric', ['attribute' => 'Đầu điểm 1']);
        // }

        // if ($row['BE']) {
        //     $errors[] = __('validation.numeric', ['attribute' => 'Đầu điểm 2']);
        // }

        // if ($row['BF']) {
        //     $errors[] = __('validation.numeric', ['attribute' => 'Đầu điểm 3']);
        // }

        // if ($row['BG']) {
        //     $errors[] = __('validation.numeric', ['attribute' => 'Điểm xét tuyển']);
        // }

        return [
            $errors,
            $birthday,
            $grant_date,
        ];
    }

    public function createAccount($firstname, $lastname, $birthday): string
    {
        $account = strtolower(convert_str($lastname) . implode(array_map(function ($item) {
            $item = trim($item);

            if (!$item) {
                return '';
            }

            return substr(convert_str($item), 0, 1);
        }, explode(' ', $firstname))));
        if (preg_match('/^(\d?\d)[\/\-\.\s](\d?\d)[\/\-\.\s](\d{4})$/', $birthday, $matches)) {
            $account = sprintf('%s%02d%02d%02d', $account, $matches[1], $matches[2], substr($matches[3], -2));
        } else {
            $account .= "00$birthday";
        }

        $number = 1;
        $valid_account = $account;

        $exist_accounts = $this->student_repository->getAccount();
        while (in_array($valid_account, $exist_accounts, true)) {
            $number++;
            $valid_account = $account . $number;
        }

        $exist_accounts[] = $valid_account;

        return $valid_account;
    }
}
