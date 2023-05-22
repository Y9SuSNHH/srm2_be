<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Factories;

use App\Helpers\CsvParser;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Grade\GradeRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Grade\ImportRequest;
use App\Http\Domain\TrainingProgramme\Services\LearningModuleService;
use App\Http\Enum\GradeDiv;
use App\Http\Enum\GradeSettingDiv;
use Illuminate\Support\Facades\Log;

class GradeServiceUNETI implements GradeServiceInterface
{
    private $errors;
    private $preview;
    private $data;
    private $grade_values;
    private $grade_repository;
    private $student_repository;

    /**
     * GradeServiceTVU constructor.
     * @param GradeRepositoryInterface $grade_repository
     * @param StudentRepositoryInterface $student_repository
     */
    public function __construct(GradeRepositoryInterface $grade_repository, StudentRepositoryInterface $student_repository)
    {
        $this->errors = [];
        $this->preview = [];
        $this->data = [];
        $this->grade_values = [];
        $this->grade_repository =$grade_repository;
        $this->student_repository = $student_repository;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getPreview(): array
    {
        return $this->preview;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getGradeValues(): array
    {
        return $this->grade_values;
    }

    public function processUploadFile(ImportRequest $request): bool
    {
        try {
            $student_codes = [];
            $csv = new CsvParser($request->file, [], 10);
            $get_student_codes = new CsvParser($request->file, [], 10);

            /** @var LearningModuleService $learning_module_service */
            $learning_module_service = app()->service(LearningModuleService::class);
            $learning_module = $learning_module_service->getById($request->learning_module_id);
            $exam_date = $request->exam_date;
            $grade_exists = array_unique($this->grade_repository->getGradeExists($learning_module['id'], $exam_date)->transform(function ($grade) {
                /** @var \App\Eloquent\Grade $grade */
                return implode([$grade->learning_module_id, $grade->exam_date->toDateString(), $grade->student_id]);
            })->toArray());
            /** @var \Illuminate\Database\Eloquent\Collection $exam_plan */

            $get_student_codes->each(function ($row,$index) use(&$student_codes) {
                $row = array_map('trim', $row);
                $text = implode(array_filter($row));
                if(preg_match('/^Tổng cộng:\d+$/', $text)) {
                    return false;
                }

                array_push($student_codes,$row['B']);
                return true;
            });

            $students = $this->student_repository->findExistedStudents($student_codes)->keyBy('student_code')->toArray();

            $csv->each(function ($row, $index) use (&$grade_exists, $students, $learning_module, $exam_date) {
                $row = array_map('trim', $row);

                $text = implode(array_filter($row));
                if(preg_match('/^Tổng cộng:\d+$/', $text)) {
                    return false;
                }

                $error = [];
                if (empty(trim($row['B']))) {
                    $error[] = __('validation.required', ['attribute' => 'Mã SV']);
                } else {
                    $student = !isset($students[$row['B']]) ? null : $students[$row['B']];
                    if (null === $student) {
                        $error[] = __('validation.in', ['attribute' => 'Mã SV']);
                    } else {
                        $check = implode([$learning_module['id'], $exam_date->toDateString(), $student['id']]);

                        if (in_array($check, $grade_exists)) {
                            $error[] = 'Sinh viên đã có điểm.';
                        } else {
                            $grade_exists[] = $check;
                        }
                    }
                }

                if (!empty($error)) {
                    $this->errors[$index] = $error;
                    return null;
                }

                $ipkey = sprintf('%s.%s.%s', $learning_module['id'], $student['id'], $exam_date->toDateString());
                $this->data[] = [
                    'learning_module_id' => $learning_module['id'],
                    'student_id' => $student['id'],
                    'exam_date' => $exam_date->toDateString(),
                    'note' => $row['T'] ?? '',
                    'ipk' => $ipkey,
                ];



                // grade_values
                $this->grade_values[] = [ 'grade_div' => GradeDiv::DIEM_CHUYEN_CAN, 'grade_id' => $ipkey, 'value' => $row['H'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::DIEM_THUONG_XUYEN_1, 'grade_id' => $ipkey, 'value' => $row['I'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::DIEM_THUONG_XUYEN_2, 'grade_id' => $ipkey, 'value' => $row['J'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::DUOC_DU_THI, 'grade_id' => $ipkey, 'value' => $row['K'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::DIEM_CUOI_KY_1, 'grade_id' => $ipkey, 'value' => $row['L'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::DIEM_CUOI_KY_2, 'grade_id' => $ipkey, 'value' => $row['M'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::VANG_THI, 'grade_id' => $ipkey, 'value' => $row['N'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::DIEM_TONG_KET_1, 'grade_id' => $ipkey, 'value' => $row['O'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::DIEM_TONG_KET_2, 'grade_id' => $ipkey, 'value' => $row['P'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::THANG_DIEM_4, 'grade_id' => $ipkey, 'value' => $row['Q'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::DIEM_CHU, 'grade_id' => $ipkey, 'value' => $row['R'] ?? ''];
                $this->grade_values[] = [ 'grade_div' => GradeDiv::XEP_LOAI, 'grade_id' => $ipkey, 'value' => $row['S'] ?? ''];

                // $diem_ly_thuyet = '';
                // $diem_thuc_hanh = '';
                // if (GradeSettingDiv::FIVE === $learning_module['grade_setting_div']) {
                //     $diem_ly_thuyet = preg_match('/^([\d\.]+)$/', $row['H']) ? (float)$row['H'] : null;
                //     $this->grade_values[] = [
                //         'grade_div' => GradeDiv::DIEM_LY_THUYET,
                //         'grade_id' => $ipkey,
                //         'value' => $diem_ly_thuyet,
                //     ];

                //     $diem_thuc_hanh = preg_match('/^([\d\.]+)$/', $row['I']) ? (float)$row['I'] : null;
                //     $this->grade_values[] = [
                //         'grade_div' => GradeDiv::DIEM_THUC_HANH,
                //         'grade_id' => $ipkey,
                //         'value' => $diem_thuc_hanh,
                //     ];
                // }

                // $exam_grade = $row[GradeSettingDiv::THREE === $learning_module['grade_setting_div'] ? 'H' : 'J'];
                // if (preg_match('/^([\d\.]+)$/', $exam_grade)) {
                //     $exam_grade = (float)$exam_grade;
                // } else {
                //     $exam_grade = null;
                // }

                // $this->grade_values[] = [
                //     'grade_div' => GradeDiv::EXAM_GRADE,
                //     'grade_id' => $ipkey,
                //     'value' => $exam_grade,
                // ];
                // $summary_grade = $row[GradeSettingDiv::THREE === $learning_module['grade_setting_div'] ? 'I' : 'K'];
                // if (preg_match('/^([\d\.]+)$/', $summary_grade)) {
                //     $summary_grade = (float)$summary_grade;
                // } else {
                //     $summary_grade = null;
                // }

                // $this->grade_values[] = [
                //     'grade_div' => GradeDiv::SUMMARY_GRADE,
                //     'grade_id' => $ipkey,
                //     'value' => $summary_grade,
                // ];

                $this->preview[] = [
                    'subjectName' => $learning_module['subject_name'],
                    'code' => $learning_module['code'],
                    'date' => $exam_date->toAtomString(),
                    'amountCredit' => $learning_module['amount_credit'],
                    'classroom' => $student['classrooms'][0]['code'] ?? null,
                    'studentCode' => $student['student_code'],
                    'firstname' => $student['student_profile']['profile']['firstname'],
                    'lastname' => $student['student_profile']['profile']['lastname'],
                    'gender' => $student['student_profile']['profile']['gender'],
                    'birthday' => $student['student_profile']['profile']['birthday'] ?? $student['student_profile']['profile']['borned_year'],
                    'note' => $row['T'] ?? '',
                    'count' => $learning_module['grade_setting_div'],
                    'grades' => [
                        'diemChuyenCan' => $row['H'] ?? '',
                        'diemThuongXuyen1' => $row['I'] ?? '',
                        'diemThuongXuyen2' => $row['J'] ?? '',
                        'duocDuThi' => $row['K'] ?? '',
                        'diemCuoiKy1' => $row['L'] ?? '',
                        'diemCuoiKy2' => $row['M'] ?? '',
                        'vangThi' => $row['N'] ?? '',
                        'diemTongKet1' => $row['O'] ?? '',
                        'diemTongKet2' => $row['P'] ?? '',
                        'thangDiem4' => $row['Q'] ?? '',
                        'diemChu' => $row['R'] ?? '',
                        'xepLoai' => $row['S'] ?? '',
                    ]
                ];

                return true;
            });

            return true;
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            return false;
        }
    }
}