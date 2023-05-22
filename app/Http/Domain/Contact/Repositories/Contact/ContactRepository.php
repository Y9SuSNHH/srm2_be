<?php

namespace App\Http\Domain\Contact\Repositories\Contact;

use App\Eloquent\Contact;
use App\Eloquent\Staff;
use App\Http\Domain\Contact\Models\Contact\Contact as ContactModel;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Domain\Contact\Requests\Contact\SearchRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Eloquent\BlacklistToken;

class ContactRepository implements ContactRepositoryInterface
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
        $this->model_eloquent = Contact::query()->getModel();
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

        $query->with('school')->where("school_id", school()->getId())->orderBy('id', 'desc');

        $admin = $this->isAdmin();

        if(!$admin){
            $staff_id = $this->getStaffId();
            $query->where('staff_id',$staff_id);
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
        $data->getCollection()->transform(function ($contact) {
            return new ContactModel($contact);
        });

        return $data;
    }

    /**
     * @param array $validator
     * @return array
     */
    public function create(array $validator): array
    {
        try {

            $staff_id = $this->getStaffId();
            
            $array_name = explode(' ', $validator['fullname']);
            $validator['lastname'] = trim(array_pop($array_name));
            $validator['firstname']  = trim(implode(' ', $array_name));
            $validator['created_by'] = auth()->getId();
            $validator['staff_id'] = $staff_id;
            $validator['status'] = 1;
            $contact = $this->model_eloquent->create($validator);
            $this->contactStatus($validator['firstname'], $validator['lastname'], $validator['phone_number']);
            return (array)new ContactModel($contact);
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
            $array_name = explode(' ', $validator['fullname']);
            $validator['lastname'] = trim(array_pop($array_name));
            $validator['firstname']  = trim(implode(' ', $array_name));
            $validator['updated_by'] = auth()->getId();

            $old_info = Contact::query()->findOrFail($id,['firstname', 'lastname', 'phone_number', 'school_id', 'link']);
            
            $contact = Contact::query()->findOrFail($id);

            $validator['status'] = 1;
            $contact->update($validator);
            $update = $contact->update($validator);
            if ($update && $validator['school_id'] != $old_info->school_id) {
                if($old_info->link){
                    $query_string = explode("?", $old_info->link)[1];
                    $token = explode("&", $query_string)[0];
                    $token = explode("=", $token)[1];
                    $token_part = explode('.', $token);
                    $signature = $token_part[2];
                    BlacklistToken::query()->create(['signature' => $signature]);
                    $contact->update(['link'=> null]);
                }
            }

            $this->contactStatus($validator['firstname'], $validator['lastname'], $validator['phone_number']);
            
            if($validator['firstname'] != $old_info->firstname || $validator['lastname'] != $old_info->lastname || $validator['phone_number'] != $old_info->phone_number){
                $this->newStatus($old_info->firstname, $old_info->lastname, $old_info->phone_number);
            }
            return (array)new ContactModel($contact);
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
            $old_info = Contact::query()->findOrFail($id,['firstname', 'lastname', 'phone_number']);
            Contact::query()->findOrFail($id)->delete();
            $this->newStatus($old_info->firstname, $old_info->lastname, $old_info->phone_number);
            return (array)'Delete successful';
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public function link(int $id): array
    {
        try {
            $contact = Contact::query()->findOrFail($id);
            $data = $contact->toArray();
            if($data['status'] == 2){
                throw new \Exception('Contact trùng SĐT, vui lòng kiểm tra lại!');
            }
            if($data['status'] == 3){
                $staff_name = Contact::join('staffs', 'sv50_contacts.staff_id', '=', 'staffs.id')->where('phone_number',$data['phone_number'])->whereNotNull('link')->pluck('fullname')->first();
                if($staff_name){
                    throw new \Exception($staff_name);
                }
            }
            $token = token_form_register([$id, $data['staff_id']]);
            $contact->update(['link' => env('REGISTER_URL').'?token='.$token.'&school='.$data['school_id']]);
            
            return (array)new ContactModel($contact);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => $e->getMessage()]));
        }
    }

    /**
     * @param array $staff_usernames
     * @return array
     */
    public function getStaffs(array $staff_usernames = []): array
    {
        return Staff::query()
            ->join('users', 'users.id', '=', 'staffs.user_id')
            ->whereIn('users.username', $staff_usernames)
            ->select(['users.username', 'staffs.id'])
            ->distinct()
            ->pluck('id', 'username')
            ->toArray();
    }

    public function getStaffId(string $staff_username = ''){
        $userid = auth()->getId();
        $staff_id = Staff::whereHas('user', function ($query) use ($userid, $staff_username) {
            if(!$staff_username){
                $query->where('id', $userid);
            }
            else $query->where('username', $staff_username);
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
    
    public function contactStatus(string $firstname, string $lastname, string $phone_number){

        $data_duplicate_phone = Contact::where('phone_number',$phone_number)->pluck('id')->all();

        if(count($data_duplicate_phone)>1){
            Contact::whereIn('id', $data_duplicate_phone)->where('status',1)->update(['status' => 2]);
            $data_duplicate_contact = Contact::where('phone_number',$phone_number)->where('firstname', 'ilike' ,$firstname)->where('lastname', 'ilike', $lastname)->pluck('id')->all();
            if(count($data_duplicate_contact)>1){
                Contact::whereIn('id', $data_duplicate_contact)->update(['status' => 3]);
            }
        }
    }

    public function newStatus(string $firstname, string $lastname, string $phone_number){
        $data_duplicate_contact = Contact::where('phone_number',$phone_number)->where('firstname', 'ilike', $firstname)->where('lastname', 'ilike', $lastname)->pluck('id')->all();
        $data_duplicate_phone = Contact::where('phone_number',$phone_number)->pluck('id')->all();
        if(count($data_duplicate_contact)==1){
            if(count($data_duplicate_phone)==1){
                Contact::where('id', $data_duplicate_contact[0])->update(['status' => 1]);
            }
            else{
                Contact::where('id', $data_duplicate_contact[0])->update(['status' => 2]);
            }
        }

        $data_duplicate_phone = Contact::where('phone_number',$phone_number)->pluck('id')->all();
        if(count($data_duplicate_phone)==1){
            Contact::where('id', $data_duplicate_phone[0])->update(['status' => 1]);
        }
    }

    public function insert(array $attribute): bool
    {
        return $this->model_eloquent->newQuery()->insert($attribute);
    }
}
