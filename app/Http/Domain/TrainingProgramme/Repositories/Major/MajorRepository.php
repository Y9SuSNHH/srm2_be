<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Major;

use App\Eloquent\Major;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\TrainingProgramme\Requests\Major\CreateMajorRequest;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Http\Domain\TrainingProgramme\Models\Major\Major as MajorModel;

class MajorRepository implements MajorRepositoryInterface
{
    use ThrowIfNotAble;

    /**
     * @var Builder|Model
     */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = Major::query()->getModel();
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return Major::query()->with('getObjects')->orderBy('name')->get()->transform(function ($major) {
            return [
                'id' => $major->id,
                'school_id' => $major->school_id ,
                'area_id' => $major->area_id ,
                'code' => $major->code ,
                'name' => $major->name ,
                'shortcode' => $major->shortcode ,
                'objects' => $major->getObjects->map(function ($object) {
                    return $object->shortcode;
                })->toArray()
            ];
        })->toArray();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        $major = Major::query()->findOrFail($id)->toArray();
        return (array)new MajorModel($major);
    }

    /**
     * @param CreateMajorRequest $request
     * @return array
     */
    public function create(CreateMajorRequest $request): array
    {
        return $this->createAble(Major::class, function () use ($request) {
            $major = $this->model->create($request->validated())->toArray();
            return (array)new MajorModel($major);
        });
    }

    /**
     * @param int $id
     * @param CreateMajorRequest $request
     * @return array
     */
    public function update(int $id, CreateMajorRequest $request): array
    {
        return $this->updateAble(Major::class, function () use ($id, $request) {
            $major = Major::query()->findOrFail($id);
            $major->update($request->all());
            $major = Major::query()->findOrFail($id)->toArray();
            return (array)new MajorModel($major);
        });
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->deleteAble(Major::class, function () use ($id) {
            Major::query()->findOrFail($id)->delete();
            return true;
        });
    }

    public function findMajorByShortcodes(array $shortcodes) {
        $majors = $this->model->query()
                       ->whereIn('shortcode',$shortcodes)
                       ->get();
        return $majors;
    }
}