<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Subject;

use App\Eloquent\Subject;
use App\Http\Domain\TrainingProgramme\Models\Subject\Subject as SubjectModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Domain\TrainingProgramme\Requests\Subject\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class SubjectRepository implements SubjectRepositoryInterface
{
    /** @var Builder|\Illuminate\Database\Eloquent\Model */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = Subject::query()->getModel();
    }

    /**
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator
    {
        $query = $this->SubjectRepositoryQuery($request);
        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        $paginate->getCollection()->transform(function ($subject) {
            return new SubjectModel($subject);
        });
        return $paginate;
    }

    /**
     * @param SearchRequest $request
     * @return array
     */
    public function options(SearchRequest $request): array
    {
        return $this->SubjectRepositoryQuery($request)
            ->get()
            ->transform(function ($subject) {
                return new SubjectModel($subject);
            })
            ->toArray();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        $subject = Subject::query()->findOrFail($id);
        return (array)new SubjectModel($subject);
    }

    /**
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array
    {
        try {
            $validator['school_id'] = school()->getId();
            $subject = $this->model->create($validator);
            return (array)new SubjectModel($subject);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param int $id
     * @param array $validator
     * @return array
     */
    public function update(int $id, array $validator): array
    {
        try {
            $subject = Subject::query()->findOrFail($id);
            $subject->update($validator);
            $subject = Subject::query()->findOrFail($id);
            return (array)new SubjectModel($subject);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        try {
            Subject::query()->findOrFail($id)->delete();
            return (array)'delete successful';
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param SearchRequest $request
     * @return Builder|\Illuminate\Database\Eloquent\Model
     */
    private function SubjectRepositoryQuery(SearchRequest $request): Builder|\Illuminate\Database\Eloquent\Model
    {
        $query = $this->model->newQuery()->orderBy('id', 'desc');
      
        if ($request->keyword) {
            $query->where('name', 'ilike', '%' . $request->keyword . '%');
        }

        return $query;
    }
}