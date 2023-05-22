<?php

namespace App\Http\Domain\Student\Repositories\StudentProfile;

use App\Eloquent\StudentProfile;
use App\Helpers\Traits\ThrowIfNotAble;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ReflectionException;
use Throwable;

class StudentProfileRepository implements StudentProfileRepositoryInterface
{
    use ThrowIfNotAble;

    private Builder|Model $eloquent_model;
    private string $model;

    public function __construct()
    {
        $this->model          = StudentProfile::class;
        $this->eloquent_model = StudentProfile::query()->getModel();
    }

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    public function update(int $id, array $data): mixed
    {
        return $this->updateAble($this->model, function () use ($id, $data) {
            return $this->eloquent_model->newQuery()->findOrFail($id)->updateOrFail($data);
        });
    }

    public function getByProfileCode(string $profile_code)
    {
        return StudentProfile::query()
            ->where('profile_code',$profile_code)->get();
    }
}