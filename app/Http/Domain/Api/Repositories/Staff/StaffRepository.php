<?php

namespace App\Http\Domain\Api\Repositories\Staff;

use App\Eloquent\Staff;
use App\Http\Domain\Api\Models\Staff\Staff as ModelsStaff;
use App\Http\Domain\Api\Requests\Staff\SearchRequest;
use App\Http\Enum\StaffTeam;

class StaffRepository implements StaffRepositoryInterface
{
    private $eloquent_model;

    public function __construct()
    {
        $this->eloquent_model = Staff::query()->getModel();
    }

    public function getAll()
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @param SearchRequest $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[]|mixed
     */
    public function getOptions(SearchRequest $request)
    {
        $query = $this->eloquent_model->newQuery()->select(['id', 'fullname', 'team']);

        if ($request->team) {
            $query->where('team', $request->team);
        }

        return $query->get()->transform(function ($staff) {
            return (new ModelsStaff($staff))->toArray(['id', 'fullname','team']);
        });
    }    
    /**
     * getAllStaff
     *
     * @return void
     */
    public function getStaff()
    {
        $result = $this->eloquent_model->newQuery()->get();
        return $result;
    }

    public function findStaff(array $usernames) {
        $staffs = $this->eloquent_model->query()
        ->with(['user' => function ($q) use ($usernames) {
            $q->select('id', 'username');
            $q->whereIn('username',$usernames);
        }])
        ->where('team', StaffTeam::ADMISSION_ADVISER)
        ->get();
        return $staffs;
    }
}