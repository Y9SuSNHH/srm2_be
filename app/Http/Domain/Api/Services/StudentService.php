<?php

namespace App\Http\Domain\Api\Services;

use App\Http\Domain\Api\Repositories\Student\GradeRepositoryInterface;
use App\Http\Enum\GradeDiv;

class StudentService
{
    /** @var \App\Http\Domain\Api\Repositories\Student\GradeRepository */
    private $grade_repository;

    public function __construct(GradeRepositoryInterface $grade_repository)
    {
        $this->grade_repository = $grade_repository;
    }

    /**
     * @param string $student_code
     * @return array
     */
    public function gradeTVU(string $student_code): array
    {
        $results = [];
        /** @var \App\Eloquent\Grade[]|\Illuminate\Database\Eloquent\Collection $grades */
        $grades = $this->grade_repository->getStudentGrades($student_code);
        $grades->each(function ($grade) use (&$results, $student_code) {
            /** @var \App\Eloquent\Grade $grade */
            $learning_module = $grade->learningModule;
            $mon_hoc = sprintf('%s (%s)', $learning_module->subject->name ?? $learning_module->alias, $learning_module->code);
            $grade_values = $grade->gradeValues->keyBy('grade_div')->map(function ($value) {
                return $value->value;
            })->toArray();

            $results[] = [
                'mon_hoc' => $mon_hoc,
                'ngay_thi' => $grade->exam_date->format('d/m/Y'),
                'ma_sinh_vien' => $student_code,
                'diem_tb_qua_trinh' => array_get($grade_values, GradeDiv::PROCESS_AVERAGE_GRADE),
                'diem_kiem_tra' => array_get($grade_values, GradeDiv::EXAM_GRADE),
                'diem_tong_ket' => array_get($grade_values, GradeDiv::SUMMARY_GRADE),
                'diem_lt' => array_get($grade_values, GradeDiv::DIEM_LY_THUYET),
                'diem_th' => array_get($grade_values, GradeDiv::DIEM_THUC_HANH),
                'ghi_chu' => $grade->note,
            ];
        });

        return $results;
    }
}
