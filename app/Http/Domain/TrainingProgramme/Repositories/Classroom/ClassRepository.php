<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Classroom;

use App\Eloquent\Classroom as EloquentClassroom;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ClassRepository
 * @package App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom
 */
class ClassRepository implements ClassRepositoryInterface
{
    /** @var Builder|\Illuminate\Database\Eloquent\Model|EloquentClassroom */
    private $eloquent_model;

    public function __construct()
    {
        $this->eloquent_model = EloquentClassroom::query()->getModel();
    }

    /**
     * @return array
     */
    public function getClassroomCode(): array
    {
        return $this->eloquent_model->pluck('id', 'code')->toArray();
    }
}
