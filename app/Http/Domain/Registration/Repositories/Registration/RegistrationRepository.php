<?php

namespace App\Http\Domain\Registration\Repositories\Registration;

use App\Eloquent\Registration;
use App\Eloquent\Staff;
use App\Http\Domain\Registration\Models\Registration\Registration as RegistrationModel;
use App\Http\Domain\Registration\Models\Info\RegistrationInfo as InfoModel;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Domain\Registration\Requests\Registration\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class RegistrationRepository implements RegistrationRepositoryInterface
{
    /**
     * @var Builder|Model
     */
    private Builder|Model $model_eloquent;

    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->model_eloquent = Registration::query()->getModel();
    }

    /**
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     * @throws ValidationException
     */

    public function getAll(SearchRequest $request): LengthAwarePaginator
    {
        $per_page = $request->perPage();
        $request  = $request->validated();
        $query = $this->model_eloquent->clone();

        $query->with(['major:id,name', 'area:id,code'])->orderBy('id', 'desc');

        $query->whereHas('major', function ($q) {
            $q->where('school_id', school()->getId());
        });

        $admin = $this->isAdmin();

        if(!$admin){
            $staff_id = $this->getStaffId();
            $query->where('staff_id', $staff_id);
        }

        if (!empty($request['fullname'])) {
            $fullname = trim(mb_strtolower($request['fullname'], 'UTF-8'));
            $query->when($fullname, function ($q) use ($fullname) {
                $q->where(DB::raw("lower(CONCAT(firstname, ' ', lastname))"), "LIKE", "%$fullname%");
            });
        }
        if (!empty($request['phone_number'])) {
            $phone_number = trim($request['phone_number']);
            $query->when($phone_number, function ($q) use ($phone_number) {
                $q->where('phone_number', "LIKE", "%$phone_number%");
            });
        }

        $data = $query->makePaginate($per_page);
        $data->getCollection()->transform(function ($registration) {
            return new RegistrationModel($registration);
        });

        return $data;
    }

    /**
     * @param int $id
     * @param array $validator
     * @return array
     */
    public function update(int $id, array $validator): array
    {
        try {
            $validator['updated_by'] = auth()->getId();
            $validator['identification_info'] = json_encode($validator['identification_info']);
            $validator['residence'] = json_encode($validator['residence']);
            $validator['address'] = json_encode($validator['address']);
            $validator['graduate'] = json_encode($validator['graduate']);
            $validator['curriculum_vitae'] = json_encode($validator['curriculum_vitae']);
            
            $registration = Registration::query()->findOrFail($id);
            $registration->update($validator);

            return (array)new RegistrationModel($registration);
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
            Registration::query()->findOrFail($id)->delete();
            return (array)'Delete successful';
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    public function getStaffId(){
        $userid = auth()->getId();
        $staff_id = Staff::whereHas('user', function ($query) use ($userid) {
            $query->where('id', $userid);
        })->where('status','working')->value('id');

        return $staff_id;
    }

    public function isAdmin(){
        $userid = auth()->getId();
        $admin = Staff::whereHas('user', function ($query) use ($userid) {
            $query->where('id', $userid);
        })->where('status','working')->where('team','admin')->value('id');

        return $admin;
    }

    public function getById(int $id): array
    {
        $registration = Registration::query()->with('staff:id,fullname')->findOrFail($id);
        return (array)new InfoModel($registration);
    }
}
