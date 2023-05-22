<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Period;

use App\Eloquent\Period;
use App\Http\Domain\TrainingProgramme\Models\Period\Period as PeriodModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Domain\TrainingProgramme\Requests\Period\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class PeriodsRepository implements PeriodsRepositoryInterface
{
    /** @var Builder|\Illuminate\Database\Eloquent\Model */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = Period::query()->getModel();
    }

    public function getAll(SearchRequest $request): LengthAwarePaginator
    {
        $query = $this->PeriodRepositoryQuery($request);
        /** @var LengthAwarePaginator $paginate */
        $paginate = $query->makePaginate($request->perPage());
        $paginate->getCollection()->transform(function ($period) {
            return new PeriodModel($period);
        });
        return $paginate;
    }

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function options(SearchRequest $request): mixed
    {
        $query = $this->PeriodRepositoryQuery($request);

        return $query->get()->mapInto(PeriodModel::class);
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
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param SearchRequest $request
     * @return Builder|\Illuminate\Database\Eloquent\Model
     */
    private function PeriodRepositoryQuery(SearchRequest $request): Builder|\Illuminate\Database\Eloquent\Model
    {
        $query = $this->model->newQuery()->with('classroom')->orderBy('id', 'desc');
        if ($request->semester) {
            $query->where('semester', $request->semester);
        }
        if ($request->classroom_id) {
            $query->whereIn('classroom_id', explode(',',$request->classroom_id));
        }
        if ($request->learn_began_date) {
            $query->whereDate('learn_began_date', $request->learn_began_date);
        }
        if ($request->collect_began_date) {
            $query->whereDate('collect_began_date', $request->collect_began_date);
        }
        if ($request->decision_date) {
            $query->whereDate('decision_date', $request->decision_date);
        }
        if (!is_null($request->is_final)) {
            $query->where('is_final', $request->is_final);
        }

        return $query;
    }
}
