<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Services;

use App\Helpers\CsvParser;
use App\Http\Domain\AcademicAffairsOfficer\Factories\GradeServiceInterface;
use App\Http\Domain\AcademicAffairsOfficer\Models\Grade\Grade;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom\ClassRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom\ClassRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Grade\GradeRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Student\StudentRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\StudyPlan\StudyPlanRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\StudyPlan\StudyPlanRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Grade\ImportRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Grade\SearchRequest;
use App\Http\Enum\GradeDiv;
use App\Http\Enum\GradeSettingDiv;
use Illuminate\Support\Facades\DB;

/**
 * Class StudentGradeService
 * @package App\Http\Domain\AcademicAffairsOfficer\Services
 */
class StudentGradeService
{
    /** @var StudyPlanRepository */
    private $study_plan_repository;
    /** @var StudentRepository */
    private $student_repository;
    /** @var ClassRepository */
    private $class_repository;

    public function __construct(StudyPlanRepositoryInterface $repository, StudentRepositoryInterface $student_repository, ClassRepositoryInterface $class_repository)
    {
        $this->study_plan_repository = $repository;
        $this->student_repository = $student_repository;
        $this->class_repository = $class_repository;
    }

    /**
     * @param GradeRepositoryInterface $grade_repository
     * @param SearchRequest $request
     * @return array|null
     */
    public function getStudentGrade(GradeRepositoryInterface $grade_repository, SearchRequest $request): ?array
    {
        $grades = $grade_repository->getAll($request);
        /** @var \App\Eloquent\Grade $grade */

        $grades->getCollection()->transform(function ($item) {
            return new Grade($item);
        });

        return [$grades];
    }

    /**
     * @return array
     */
    public function getExamDone(): array
    {
//        $exams = $this->study_plan_repository->getExam(true);
//        $results = [];
//
//        /** @var \App\Eloquent\StudyPlan $exam */
//        foreach ($exams as $exam) {
//            $id = $exam->learningModule->id;
//            $date = $exam->day_of_the_test->toAtomString();
//
//            if (!isset($results[$id])) {
//                $results[$id] = [
//                    'name' => $exam->learningModule->name,
//                    'code' => $exam->learningModule->code,
//                    'amountCredit' => $exam->learningModule->amount_credit,
//                    'gradeSettingDiv' => $exam->learningModule->grade_setting_div,
//                    'dates' => [$date => ''],
//                    'classrooms' => [$exam->classroom_id => $exam->classroom->code ?? null],
//                ];
//            } else {
//                $results[$id]['dates'][$date] = '';
//                $results[$id]['classrooms'][$exam->classroom_id] = $exam->classroom->code ?? null;
//            }
//        }
//
//        return ($results);
    }

    /**
     * @param ImportRequest $request
     * @param GradeRepositoryInterface $grade_repository
     * @return array
     */
    public function analyzing(ImportRequest $request, GradeRepositoryInterface $grade_repository): array
    {
        $paths = explode('\\', GradeServiceInterface::class);
        $name = array_pop($paths);
        $classname = sprintf('%s\\%s', implode('\\', $paths), str_replace('Interface', school()->getCode(), $name));
        /** @var GradeServiceInterface $service */
        $service = app()->service($classname);
        $service->processUploadFile($request);

        return [
            $service->getErrors(),
            $service->getPreview(),
            $service->getData(),
            $service->getGradeValues(),
        ];
//        $errors = [];
//        $preview = [];
//        $data = [];
//        $grade_values = [];
//        $student_codes = [];
//        $csv = new CsvParser($request->file, [], 14);
//        $get_student_codes = new CsvParser($request->file, [], 14);
//        /** @var \App\Eloquent\LearningModule $learning_module */
//        $learning_module = $this->study_plan_repository->findLearningModule($request->learning_module_id);
//        $exam_date = $request->exam_date;
//        $setting = $grade_repository->getSetting($learning_module->id)
//            ->keyBy('id')->transform(function ($item) {
//                return $item['learning_module_id'] .','. $item['grade_div'];
//            })->toArray();
//
//        $setting = array_flip($setting);
//        $grade_exists = array_unique($grade_repository->getGradeExists($learning_module->id, $exam_date)->transform(function ($grade) {
//            /** @var \App\Eloquent\Grade $grade */
//            return implode([$grade->learning_module_id, $grade->exam_date->toDateString(), $grade->student_id]);
//        })->toArray());
//        /** @var \Illuminate\Database\Eloquent\Collection $exam_plan */
//        // $exam_plan = $this->study_plan_repository->findExamPlan($learning_module->id, $exam_date);
//        // $classrooms = array_unique($exam_plan->map(fn($e) => $e->classroom->id ?? null)->toArray());
//        // $students = $this->student_repository->getContestStudentList($classrooms, $exam_date)->keyBy('student_code')->toArray();
//
//        $get_student_codes->each(function ($row,$index) use(&$student_codes) {
//            $row = array_map('trim', $row);
//            if (empty(array_filter($row))) {
//                return false;
//            }
//            array_push($student_codes,$row['B']);
//            return true;
//        });
//
//        $students = $this->student_repository->findExistedStudents($student_codes)->keyBy('student_code')->toArray();
//
//        $csv->each(function ($row, $index) use (&$errors, &$preview, &$data, &$grade_values, &$grade_exists, $students, $learning_module, $exam_date, $setting) {
//            $row = array_map('trim', $row);
//
//            if (empty(array_filter($row))) {
//                return false;
//            }
//
//            $error = [];
//            if (empty(trim($row['B']))) {
//                $error[] = __('validation.required', ['attribute' => 'Mã SV']);
//            } else {
//                $student = !isset($students[$row['B']]) ? null : $students[$row['B']];
//                if (null === $student) {
//                    $error[] = __('validation.in', ['attribute' => 'Mã SV']);
//                } else {
//                    $check = implode([$learning_module->id, $exam_date->toDateString(), $student['id']]);
//
//                    if (in_array($check, $grade_exists)) {
//                        $error[] = 'Sinh viên đã có điểm.';
//                    } else {
//                        $grade_exists[] = $check;
//                    }
//                }
//            }
//
//            if (!empty($error)) {
//                $errors[$index] = $error;
//                return null;
//            }
//
//            $note = $row[GradeSettingDiv::THREE === $learning_module->grade_setting_div ? 'M' : 'O'] ?? '';
//            $ipkey = sprintf('%s.%s.%s', $learning_module->id, $student['id'], $exam_date->toDateString());
//            $data[] = [
//                'learning_module_id' => $learning_module->id,
//                'student_id' => $student['id'],
//                'exam_date' => $exam_date->toDateString(),
//                'note' => $note,
//                'ipk' => $ipkey,
//            ];
//
//
//
//            // grade_values
//            $process_average_grade = preg_match('/^([\d\.]+)$/', $row['G']) ? (float)$row['G'] : null;
////            $grade_setting_id = $setting["{$learning_module->id}," . GradeDiv::PROCESS_AVERAGE_GRADE];
//            $grade_values[] = [
////                'grade_setting_id' => $grade_setting_id,
//                'grade_div' => GradeDiv::PROCESS_AVERAGE_GRADE,
//                'grade_id' => $ipkey,
//                'value' => $process_average_grade,
//            ];
//
//            $diem_ly_thuyet = '';
//            $diem_thuc_hanh = '';
//            if (GradeSettingDiv::FIVE === $learning_module->grade_setting_div) {
//                $diem_ly_thuyet = preg_match('/^([\d\.]+)$/', $row['H']) ? (float)$row['H'] : null;
////                $grade_setting_id = $setting["{$learning_module->id}," . GradeDiv::DIEM_LY_THUYET];
//                $grade_values[] = [
////                    'grade_setting_id' => $grade_setting_id,
//                    'grade_div' => GradeDiv::DIEM_LY_THUYET,
//                    'grade_id' => $ipkey,
//                    'value' => $diem_ly_thuyet,
//                ];
//
//                $diem_thuc_hanh = preg_match('/^([\d\.]+)$/', $row['I']) ? (float)$row['I'] : null;
////                $grade_setting_id = $setting["{$learning_module->id}," . GradeDiv::DIEM_THUC_HANH];
//                $grade_values[] = [
////                    'grade_setting_id' => $grade_setting_id,
//                    'grade_div' => GradeDiv::DIEM_THUC_HANH,
//                    'grade_id' => $ipkey,
//                    'value' => $diem_thuc_hanh,
//                ];
//            }
//
//            $exam_grade = $row[GradeSettingDiv::THREE === $learning_module->grade_setting_div ? 'H' : 'J'];
//            if (preg_match('/^([\d\.]+)$/', $exam_grade)) {
//                $exam_grade = (float)$exam_grade;
//            } else {
//                $exam_grade = null;
//            }
////            $grade_setting_id = $setting["{$learning_module->id}," . GradeDiv::EXAM_GRADE];
//            $grade_values[] = [
////                'grade_setting_id' => $grade_setting_id,
//                'grade_div' => GradeDiv::EXAM_GRADE,
//                'grade_id' => $ipkey,
//                'value' => $exam_grade,
//            ];
//            $summary_grade = $row[GradeSettingDiv::THREE === $learning_module->grade_setting_div ? 'I' : 'K'];
//            if (preg_match('/^([\d\.]+)$/', $summary_grade)) {
//                $summary_grade = (float)$summary_grade;
//            } else {
//                $summary_grade = null;
//            }
////            $grade_setting_id = $setting["{$learning_module->id}," . GradeDiv::SUMMARY_GRADE];
//            $grade_values[] = [
////                'grade_setting_id' => $grade_setting_id,
//                'grade_div' => GradeDiv::SUMMARY_GRADE,
//                'grade_id' => $ipkey,
//                'value' => $summary_grade,
//            ];
//
//            $preview[] = [
//                'subjectName' => $learning_module->subject->name,
//                'code' => $learning_module->code,
//                'date' => $exam_date->toAtomString(),
//                'amountCredit' => $learning_module->amount_credit,
//                'classroom' => $student['classrooms'][0]['code'] ?? null,
//                'studentCode' => $student['student_code'],
//                'firstname' => $student['student_profile']['profile']['firstname'],
//                'lastname' => $student['student_profile']['profile']['lastname'],
//                'gender' => $student['student_profile']['profile']['gender'],
//                'birthday' => $student['student_profile']['profile']['birthday'] ?? $student['student_profile']['profile']['borned_year'],
//                'processAverageGrade' => $process_average_grade,
//                'diemLyThuyet' => $diem_ly_thuyet,
//                'diemThucHanh' => $diem_thuc_hanh,
//                'examGrade' => $exam_grade,
//                'summaryGrade' => $summary_grade,
//                'note' => $note,
//                'count' => $learning_module->grade_setting_div,
//            ];
//
//            return true;
//        });
//
//        return [$errors, $preview, $data, $grade_values];
    }

    /**
     * @param int $storage_file_id
     * @param array $grades
     * @param array $grade_values
     * @param GradeRepositoryInterface $grade_repository
     * @return bool
     */
    public function store(int $storage_file_id, array $grades, array $grade_values, GradeRepositoryInterface $grade_repository): bool
    {
        $result = false;

        try {
            $result = DB::transaction(function () use ($storage_file_id, $grades, $grade_values, $grade_repository) {
                $grade_ids = $grade_repository->insertGrade($grades, $storage_file_id);

                if (empty($grade_ids)) {
                    throw_exception('Fail to insert grades table');
                }

                $grade_values = array_map(function ($item) use ($grade_ids) {
                    if (isset($grade_ids[$item['grade_id']]) && $item['value']) {
                        $item['grade_id'] = $grade_ids[$item['grade_id']];
                        return $item;
                    }

                    return null;
                }, $grade_values);

                if (!$grade_repository->insertGradeValue($grade_values)) {
                    throw_exception('Fail to insert grades table');
                }

                return true;
            });
        } catch (\Exception $exception) {
            throw_json_response($exception->getMessage());
        }

        return $result;
    }

    /**
     * @param GradeRepositoryInterface $grade_repository
     * @param int $storage_file_id
     * @return array
     */
    public function getGradeDeleted(GradeRepositoryInterface $grade_repository, int $storage_file_id): array
    {
        $grades = $grade_repository->getGradeDeleted($storage_file_id);

        return [count($grades), $grades];
    }

    /**
     * @param GradeRepositoryInterface $grade_repository
     * @param int $storage_file_id
     * @param array $grade_ids
     * @return bool
     */
    public function delete(GradeRepositoryInterface $grade_repository, int $storage_file_id, array $grade_ids): bool
    {
        return $grade_repository->delete($storage_file_id, $grade_ids);
    }
}
