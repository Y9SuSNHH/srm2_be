<?php

namespace App\Http\Domain\Student\Repositories\CareHistory;

use App\Helpers\Interfaces\ThrowIfNotAbleInterface;
use App\Helpers\Traits\ThrowIfNotAble;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquent\CareHistory;
use App\Http\Domain\Student\Models\CareHistory\CareHistory as CareHistoryModel;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Domain\Student\Requests\CareHistory\SearchRequest;

class CareHistoryRepository implements CareHistoryRepositoryInterface, ThrowIfNotAbleInterface
{
    use ThrowIfNotAble;

    /** @var Builder|\Illuminate\Database\Eloquent\Model */
    private $model;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model = CareHistory::query()->getModel();
    }

    /**
     * @param SearchRequest $request
     */
    public function getAll(SearchRequest $request)
    {
        $query = $this->careHistoryRepositoryQuery($request);
        $result = $query->get();
        $result->transform(function ($care_history) {
            return new CareHistoryModel($care_history);
        });
        return $result;
    }

    /**
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array
    {
        try {
            $care_history = $this->model->create($validator);
            return (array)new CareHistoryModel($care_history);
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
            $care_history = $this->model->findOrFail($id);
            $care_history->update($validator);
            return (array)new CareHistoryModel($care_history);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param SearchRequest $request
     * @return Builder|\Illuminate\Database\Eloquent\Model
     */
    public function careHistoryRepositoryQuery(SearchRequest $request): Builder|\Illuminate\Database\Eloquent\Model
    {
        $query = $this->model->newQuery()->orderBy('id', 'desc');

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->care_history_status !== null) {
            $query->where('status', $request->care_history_status);
        }

        return $query;
    }
}
