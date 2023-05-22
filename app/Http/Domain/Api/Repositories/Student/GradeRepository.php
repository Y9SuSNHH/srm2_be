<?php

namespace App\Http\Domain\Api\Repositories\Student;

use App\Eloquent\Grade as EloquentGrade;

class GradeRepository implements GradeRepositoryInterface
{
    /** @var EloquentGrade */
    private $eloquent_model;

    public function __construct()
    {
        $this->eloquent_model = EloquentGrade::getModel();
    }

    /**
     * @param string $student_code
     * @return mixed
     */
    public function getStudentGrades(string $student_code): mixed
    {
        return $this->eloquent_model->newQuery()
            ->with([
                'gradeValues:grade_div,grade_id,value',
                'learningModule' => function($query) {
                    /** @var \Illuminate\Database\Eloquent\Builder $query */
                    $query->with('subject:id,code,name')->select(['id', 'subject_id', 'code', 'amount_credit', 'alias']);
                }
            ])
            ->whereExists(function ($query) use ($student_code) {
                /** @var \Illuminate\Database\Eloquent\Builder $query */
                $query->selectRaw('1')->from('students')
                    ->where('student_code', 'ilike', '%'. str_replace(' ', '%', $student_code) .'%')
                    ->whereRaw('grades.student_id=students.id');
            })
            ->get(['grades.learning_module_id', 'grades.student_id', 'grades.exam_date', 'grades.note']);
    }
}
