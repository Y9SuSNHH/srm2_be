<?php

namespace App\Http\Domain\Student\Services;

use App\Eloquent\Classroom;
use App\Helpers\CsvParser;
use App\Eloquent\Profile;
use App\Eloquent\Student;
use App\Eloquent\StudentClassroom;
use App\Eloquent\StudentProfile;
use App\Http\Domain\Finance\Models\Finance\StudentByClass;
use App\Http\Domain\Student\Repositories\Classroom\ClassroomRepositoryInterface;
use App\Http\Domain\Student\Repositories\Profile\ProfileRepositoryInterface;
use App\Http\Enum\ProfileStatus;
use App\Http\Domain\Student\Requests\StudentProfile\ImportStudentRequest;
use App\Http\Domain\Student\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Student\Repositories\StudentClassroom\StudentClassroomRepositoryInterface;
use App\Http\Domain\Student\Repositories\StudentProfile\StudentProfileRepositoryInterface;
use App\Http\Domain\Student\Requests\StudentProfile\UpdateRequest;
use App\Http\Enum\StudentStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentProfileService
{
    private StudentRepositoryInterface $student_repository;
    private StudentProfileRepositoryInterface $repository;
    private ProfileRepositoryInterface $profile_repositotry;
    private StudentClassroomRepositoryInterface $student_classroom_repository;
    private ClassroomRepositoryInterface $classroom_respository;

    public function __construct(StudentRepositoryInterface $student_repository, StudentProfileRepositoryInterface $student_profile_repository,ProfileRepositoryInterface $profile_repositotry, StudentClassroomRepositoryInterface $student_classroom_repository, ClassroomRepositoryInterface $classroom_respository )
    {
        $this->student_repository = $student_repository;
        $this->repository         = $student_profile_repository;
        $this->profile_repositotry = $profile_repositotry;
        $this->student_classroom_repository = $student_classroom_repository;
        $this->classroom_respository = $classroom_respository;

    }

    public function getLabels(): array
    {
    return [
        'A' => 'Mã hồ sơ', 
        'B' => 'Ngày GV trả QĐ cho TVTS', 
        'C' => 'Trạng thái Hồ sơ giáo vụ', 
        'D' => 'Khu vực tiếp nhận hồ sơ (HCM/HN)', 
        'E' => 'Ngày nhận hồ sơ từ TVTS', 
        'F' => 'Ngày TVU trả thẻ SV', 
        'G' => 'Trạng thái HS TKTS', 
        'H' => 'Phản hồi hồ sơ lỗi giáo vụ', 
        'I' => 'Dân tộc', 
        'J' => 'Tôn giáo', 
        'K' => 'Số CMND', 
        'L' => 'Ngày cấp', 
        'M' => 'Nơi cấp', 
        'N' => 'Trường học lớp 12 bậc THPT', 
        'O' => 'Quận/huyện của Trường học lớp 12 bậc THPT', 
        'P' => 'Tỉnh/TP của Trường học lớp 12 bậc THPT', 
        'Q' => 'Công việc hiện nay -Cơ quan công tác', 
        'R' => 'Người đại diện 1', 
        'S' => 'Quan hệ', 
        'T' => 'Nghề nghiệp', 
        'U' => 'Điện thoại', 
        'V' => 'Địa chỉ', 
        'W' => 'Người đại diện 2', 
        'X' => 'Quan hệ', 
        'Y' => 'Nghề nghiệp', 
        'Z' => 'Điện thoại', 
        'AA' => 'Địa chỉ', 
        'AB' => 'Ghi chú', 
        'AC' => 'Account học tập (áp dụng UNETI)', 
        'AD' => 'Email học tập (áp dụng UNETI)', 
        'AE' => 'Mã tổ hợp xét tuyển', 
        'AF' => 'Điểm TH1', 
        'AG' => 'Điểm TH2', 
        'AH' => 'Điểm TH3', 
        'AI' => 'Điểm TB TC, CĐ, ĐH', 
        'AJ' => 'Điểm XT', 
        'AK' => 'Xếp loại tốt nghiệp', 
        'AL' => 'Cơ quan công tác',
        'AM' => 'Mã sinh viên' 
    ];
  } 

    /**
     * @param UpdateRequest $request
     * @param $student_id
     * @return array
     * @throws ValidationException
     */
    public function update(UpdateRequest $request, $student_id): array
    {
        $validated = $request->validated();
        DB::transaction(function () use ($validated, $student_id) {
            if (array_key_exists('profile_status', $validated)) {
                $this->student_repository->update($student_id, [
                    'profile_status' => $validated['profile_status'],
                ]);
            }

            if (array_key_exists('documents', $validated)) {
                $student   = $this->student_repository->getById($student_id);
                $documents = array_replace((array)$student->documents, $validated['documents']);
                $this->repository->update($student->student_profile['id'], [
                    'documents' => $documents,
                ]);
            }
        });
        return [];
    }


    
    public function analyzing(ImportStudentRequest $request, StudentRepositoryInterface $student_profile)
    {
        $errors = [];
        $preview = [];
        $datas = [];
        $data = [];
        $grade_values = [];
        $student_codes = [];
        $label_name = self::getLabels();
        $replace_labels =  [
            'A' => 'profile_code', 
            'B' => 'decision_return_date', 
            'C' => 'profile_status', 
            'D' => 'profile_receive_area', 
            'E' => 'receive_date', 
            'F' => 'student_card_received_date', 
            'G' => 'profile_status_tkts', 
            'H' => 'report_error', 
            'I' => 'nation', 
            'J' => 'religion', 
            'K' => 'identification', 
            'L' => 'grant_date', 
            'M' => 'grant_place', 
            'N' => 'high_school_name', 
            'O' => 'high_school_district', 
            'P' => 'high_school_city', 
            'Q' => 'job', 
            'R' => 'deputy_1', 
            'S' => 'deputy_relation_1', 
            'T' => 'deputy_job_1', 
            'U' => 'deputy_phone_1',  
            'V' => 'deputy_address_1', 
            'W' => 'deputy_2', 
            'X' => 'deputy_relation_2', 
            'Y' => 'deputy_job_2', 
            'Z' => 'deputy_phone_2', 
            'AA' => 'deputy_address_2', 
            'AB' => 'note', 
            'AC' => 'student_code', 
            'AD' => 'email', 
            'AE' => 'subject_code', 
            'AF' => 'grade_1', 
            'AG' => 'grade_2', 
            'AH' => 'grade_3', 
            'AI' => 'grade_avg_subject', 
            'AJ' => 'grade_subject', 
            'AK' => 'rank_subject', 
            'AL' => 'working_agency',
            'AM' => 'student_code' 
        ];
        $date_cols = ["B","E","F","L"];
        $grade_cols = ["AF","AG","AH","AI"];
        $phone_cols = ["U","Z"];

        $store_in_student_profile_table = ['A','B','D','E','F','G','H','AE','AF','AG','AH','AJ','AK'];
        $store_in_profile_table = ['I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AL'];
        $store_in_student_table = ['C','AB','AC','AD','AM'];
        $store_in_student_classroom_table = [];

        $csv = new CsvParser($request->file,self::getLabels());
        $csv->each(function ($row, $index) use (&$errors, &$preview, &$data,&$datas, $replace_labels,$date_cols,$grade_cols,$phone_cols,$label_name,$store_in_student_profile_table,$store_in_profile_table,$store_in_student_table) {
            $row = array_map('trim', $row);

            if (empty(array_filter($row))) {
                return false;
            }

            $error = [];
            $profile_code = $this->repository->getByProfileCode($row['A'])->toArray();

            // validate mã hồ sơ
            if(!empty(trim($row['A']))) {
                if(!$profile_code) {
                    $error[] = __('validation.in', ['attribute' => $label_name['A']]);
                }
            }
                        

            //validate các cột ngày tháng
            foreach($date_cols as $col) {
                if (!empty($row[$col]) && !get_carbon_vn($row[$col])) {
                    $error[] = __('validation.date_format', ['attribute' => $label_name[$col], 'format' => 'd/m/Y']);
                }
            }

            // validate ngày cấp cmnd
            if(!empty(trim($row['L']))) {
                $dateParts = explode("/", trim($row['L']));
                $row['L'] = $dateParts[2] . "-" . $dateParts[1] . "-" . $dateParts[0];
            }

            // validate trạng thái hồ sơ giáo vụ
            if(!empty(trim($row['C']))) {
                if(!ProfileStatus::existStatus($row['C'])) {
                    $error[] = __('validation.exists', ['attribute' => $label_name['C']]);
                } else {
                    $row['C'] = ProfileStatus::statusToValue($row['C']) ;
                }
            }

            // validate khu vực tiếp nhận hồ sơ
            if(!empty(trim($row['D']))) {
                if($row['D'] !== 'HN' && $row['D'] !== 'HCM') {
                    $error[] = __('validation.exists', ['attribute' => $label_name['D']]);
                }
            }


            // validate số CMND
            if(!empty(trim($row['K']))) {
                if(!is_numeric($row['K']) || !preg_match('/^\d{9}(\d{3})?$/', $row['K'])) {
                    $error[] = __('validation.not_regex', ['attribute' => $label_name['K']]);
                }
            }

            // validate các cột số điện thoại
            foreach($phone_cols as $col) {
                if(!empty(trim($row[$col]))) {
                    if(!preg_match('/(84|0[3|5|7|8|9])+([0-9]{8})\b/', $row[$col])) {
                        $error[] = __('validation.not_regex', ['attribute' => $label_name[$col]]);
                    }
                }
            }



            // validate các cột điểm
            foreach($grade_cols as $col) {
                if(!empty(trim($row[$col]))) {
                    if(!preg_match('/^(?:10|^[0-9](,[0-9]+)?)$/', $row[$col])) {
                        $error[] = __('validation.not_regex', ['attribute' => $label_name[$col]]);
                    }
                }
            }

            // validate điểm xét tuyển
            if(!empty(trim($row['AJ']))) {
                if(!preg_match('/^(?:[1-3]?[0-9](?:,[0-9]+)?|40(?:,0+)?)$/', $row['AJ'])) {
                    $error[] = __('validation.not_regex', ['attribute' => $label_name['AJ']]);
                }
            } 
            
            // validate account học tập
            if(!empty(trim($row['AC']))) {
                if(!is_numeric($row['AC'])) {
                    $error[] = __('validation.not_regex', ['attribute' => $label_name['AC']]);
                }
            }

            if(!empty(trim($row['AM']))) {
                if(!is_numeric($row['AM'])) {
                    $error[] = __('validation.not_regex', ['attribute' => $label_name['AM']]);
                }
                $student_code_in_DB = $this->student_repository->getByStudentCode([$row['AM']])->toArray();
                // dd($student_code_in_DB);
                if($student_code_in_DB) {
                    $error[] = __('validation.distinct', ['attribute' => $label_name['AM']]);
                }
            }

            if(!empty($error)) {
                $errors[$index-1] = $error;
                return null;
            }

            $preview[] = $row;
            $data['student_profile'] = [];
            $data['profile'] = [];
            $data['students'] = [];

            // lấy ra những dữ liệu cập nhật ở bảng student_profile
            foreach($store_in_student_profile_table as $value) {
                if(!empty(trim($row[$value]))) {
                    $data['student_profile'][$replace_labels[$value]] = $row[$value];
                }
            }

            // lấy ra những dữ liệu cập nhật ở bảng profile
            foreach($store_in_profile_table as $value) {
                if(!empty(trim($row[$value]))) {
                    $data['profile'][$replace_labels[$value]] = $row[$value];
                }
            }


            // lấy ra những dữ liệu cập nhật ở bảng students
            foreach($store_in_student_table as $value) {
                if(!empty(trim($row[$value]))) {
                    $data['students'][$replace_labels[$value]] = $row[$value];
                }
            }
            $datas[] = $data;
            // // lấy ra những dữ liệu cập nhật ở bảng students
            // foreach($store_in_student_classroom_table as $value) {
            //     if(!empty(trim($row[$value]))) {
            //         $data['student_classroom'][$replace_labels[$value]] = $row[$value];
            //     }
            // }
            // dd($preview);
            return true;
        });
        // dd($datas);
        return [$errors,$preview,$datas];
    }

    public function store(int $storage_file_id, array $datas) {
        $result = false;
        $curriculum_vitae_field = [
        'high_school_name', 
        'high_school_district', 
        'high_school_city', 
        'job', 
        'deputy_1', 
        'deputy_relation_1', 
        'deputy_job_1', 
        'deputy_phone_1',  
        'deputy_address_1', 
        'deputy_2', 
        'deputy_relation_2', 
        'deputy_job_2', 
        'deputy_phone_2', 
        'deputy_address_2',
        'working_agency'
        ];
        $document_field = [
            'decision_return_date', 
            'profile_receive_area', 
            'receive_date', 
            'student_card_received_date', 
            'profile_status_tkts', 
            'report_error', 
            'subject_code', 
            'grade_1', 
            'grade_2', 
            'grade_3', 
            'grade_avg_subject', 
            'grade_subject', 

        ];
        foreach($datas as $data) {
            $student_profile = $data['student_profile'];
            $profile = $data['profile'];
            $students = $data['students'];
            try {
                DB::transaction(function () use ($student_profile,$profile, $students ,$document_field,$curriculum_vitae_field ) {
                    // update dữ liệu trong bảng profile code
                    $datatoupdate = [];
                    // lấy ra dữ liệu trong DB
                    $student_profile_in_DB = $this->repository->getByProfileCode($student_profile['profile_code'])->toArray()[0];
                    if($student_profile_in_DB) {
                        $documents = json_decode($student_profile_in_DB['documents'],true);
                        foreach($student_profile as $field=>$value) {
                            if(in_array($field,$document_field)) {
                                $documents[$field] = $value;
                            }else {
                                $datatoupdate[$field] = $value;
                            }
                        }
                        $datatoupdate['documents'] = json_encode($documents);
                    }
                    // dd($student_profile_in_DB['id'],$datatoupdate);
                    if (!$this->repository->update($student_profile_in_DB['id'],$datatoupdate)) {
                        throw_json_response('insert student_profiles fail');
                    }
                    

                    // update dữ liệu trong bảng  profile
                    $datatoupdate = [];
                    $profileId = $student_profile_in_DB['profile_id'];
                    $profile_in_DB = $this->profile_repositotry->getById($profileId)->toArray();
                    if($profile_in_DB) {
                        $curriculum_vitae = json_decode($profile_in_DB['curriculum_vitae'],true);
                        foreach($profile as $field=>$value) {
                            if(in_array($field,$curriculum_vitae_field)) {
                                $curriculum_vitae[$field] = $value;
                            }else {
                                $datatoupdate[$field] = $value;
                            }
                        }
                        $datatoupdate['curriculum_vitae'] = json_encode($curriculum_vitae);
                    }
                    if(!$this->profile_repositotry->update($profileId,$datatoupdate)) {
                        throw_json_response('insert profiles fail');
                    }
    
                    // update dữ liệu trong bảng student
                    $datatoupdate = [];
                    $studentProfileId = $student_profile_in_DB['id'];
                    $student_in_DB = $this->student_repository->getByStudentProfileId($studentProfileId)->toArray()[0];
                    if($student_in_DB) {
                        foreach($students as $field=>$value) {
                                $datatoupdate[$field] = $value;
                        }
                    }
                    // dd($student_in_DB['id'],$datatoupdate);
                    if(!$this->student_repository->update($student_in_DB['id'],$datatoupdate)) {
                        throw_json_response('insert students fail');
                    }
    
    
                    // dd($student_classroom);
                    // dd($student_classroom['classroom']);
                    // $classroomId = $this->classroom_respository->getByClassroom($student_classroom['classroom'])->toArray()[0];
    
    
                    // $student_ids = $this->studentQuery()->join('student_profiles', 'students.student_profile_id', '=', 'student_profiles.id')
                    // ->where('student_profiles.profile_code',$student_profile['profile_code'])
                    // ->get('students.id',)->toArray();
                    // $id = $this->studentClassroomQuery()->where('student_id',$student_ids[0])->whereNull('ended_at')->first('id')->toArray();
                    // if($id && $classroomId['id']) {
                    //     if(!$this->studentClassroomQuery()->where('id',$id['id'])->update(['classroom_id' => $classroomId['id']])) {
                    //         throw_json_response('uypdate classroom_student fail');
                    //     }
                    // }
                    return true;
                });
            } catch(Exception $e) {
                throw_json_response($e->getMessage());
            }
        }
    }
    public function profileQuery()
    {
        return Profile::query();
    }
    public function studentQuery()
    {
        return Student::query();
    }
    public function studentProfileQuery()
    {
        return StudentProfile::query();
    }
    public function studentClassroomQuery()
    {
        return StudentClassroom::query();
    }
}