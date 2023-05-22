<?php

namespace App\Http\Domain\Student\Repositories\Classroom;


use App\Eloquent\Classroom;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Domain\Student\Models\Classroom as ClassroomModel;

class ClassroomRepository implements ClassroomRepositoryInterface
{
    protected Builder $query;
    protected string $model;

    public function __construct()
    {
        $this->model = Classroom::class;
        $this->query = Classroom::query();
    }

    public function getByIds(array $ids = [])
    {
        $data = $this->query->clone()->withTrashed()->with(['area', 'enrollmentWave'])->findMany($ids);
        $data->transform(function ($student) {
            return new ClassroomModel($student);
        });
        return $data;
    }

    public function getByClassroom(string $classroom)
    {
        return $this->query->where('code',$classroom)->get();
    }
}