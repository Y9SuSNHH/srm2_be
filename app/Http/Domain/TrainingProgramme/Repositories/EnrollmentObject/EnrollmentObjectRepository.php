<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\EnrollmentObject;

use App\Eloquent\EnrollmentObject as EloquentEnrollmentObject;
use App\Http\Domain\TrainingProgramme\Requests\EnrollmentObject\CreateEnrollmentObjectRequest;
use App\Http\Domain\TrainingProgramme\Requests\EnrollmentObject\SearchRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Http\Domain\TrainingProgramme\Models\EnrollmentObject\EnrollmentObject as EnrollmentObject;
use App\Helpers\Traits\ThrowIfNotAble;

class EnrollmentObjectRepository implements EnrollmentObjectRepositoryInterface
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
        $this->model = EloquentEnrollmentObject::query()->getModel();
    }

    /**
     * @param SearchRequest $request
     * @return array
     */
    public function getAll(SearchRequest $request): array
    {
        $query = $this->enrollmentObjectQuery($request);

        return $query->get()->transform(function ($object) {
            return new EnrollmentObject($object->toArray());
        })->toArray();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        $object = $this->model->findOrFail($id)->toArray();
        return (array)new EnrollmentObject($object);
    }

    /**
     * @param CreateEnrollmentObjectRequest $request
     * @return array
     */
    public function create(CreateEnrollmentObjectRequest $request): array
    {
        return $this->createAble(EloquentEnrollmentObject::class, function () use ($request) {
            $object = $this->model->create($request->validated())->toArray();
            return (array)new EnrollmentObject($object);
        });
        // try {
        //     $object = $this->model->create(['school_id' => \school()->getId(), ...$request->validated()])->toArray();
        //     return (array)new EnrollmentObject($object);
        // } catch (\Exception $e) {
        //     return (array)$e->getMessage();
        // }
    }

    /**
     * @param int $id
     * @param CreateEnrollmentObjectRequest $request
     * @return array
     */
    public function update(int $id, CreateEnrollmentObjectRequest $request): array
    {
        try {
            $object = $this->model->findOrFail($id);
            $object->update($request->all());
            $object = $this->model->findOrFail($id)->toArray();
            return (array)new EnrollmentObject($object);
        } catch (\Exception $e) {
            return (array)$e->getMessage();
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        try {
            $this->model->findOrFail($id)->delete();
            return (array)'delete successful';
        } catch (\Exception $e) {
            return (array)$e->getMessage();
        }
    }

    /**
     * @param SearchRequest $request
     * @return array
     */
    public function getOptions(SearchRequest $request): array
    {
        return $this->enrollmentObjectQuery($request)->orderBy('shortcode')->get()->transform(function ($object) {
            return new EnrollmentObject($object->toArray());
        })->toArray();
    }


    /**
     * @param SearchRequest $request
     * @return Builder|Model
     */
    private function enrollmentObjectQuery(SearchRequest $request): Builder|Model
    {
        $query = $this->model->newQuery();

        if ($request->major_id) {
            $query->whereExists(function ($query) use ($request) {
                /** @var Builder $query */
                $query->select('id')
                    ->from('major_object_maps')
                    ->whereRaw('major_object_maps.enrollment_object_id=enrollment_objects.id')
                    ->where('major_object_maps.major_id', $request->major_id);
            });
        }

        if ($request->keyword) {
            $query->where(function ($query) use ($request) {
                $keyword = '%' . preg_replace('/(\s+)/', '%', $request->keyword) . '%';
                /** @var Builder $query */
                $query->orWhere('code', 'ilike', $keyword);
                $query->orWhere('classification', 'ilike', $keyword);
                $query->orWhere('name', 'ilike', $keyword);
                $query->orWhere('shortcode', 'ilike', $keyword);
            });
        }

        return $query;
    }

    public function findEnrollmentObjectByShortcodes(array $shortcodes) {
        $enrollment_objects = $this->model->query()
                                          ->whereIn('shortcode',$shortcodes)
                                          ->get();
        return $enrollment_objects;
    }
}