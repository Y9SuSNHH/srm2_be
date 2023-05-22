<?php

namespace App\Http\Domain\Contact\Services;

use App\Http\Domain\Contact\Repositories\Contact\ContactRepositoryInterface;
use Carbon\Carbon;

class ContactService
{
    private $contact_repository;

    /**
     * ContactService constructor.
     * @param ContactRepositoryInterface $contact_repository
     */
    public function __construct(ContactRepositoryInterface $contact_repository)
    {
        $this->contact_repository = $contact_repository;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function insert(array $data): bool
    {
        $staff_username = array_column($data, 'staff_info');
        $staffs = $this->contact_repository->getStaffs($staff_username);
        $staff_id = auth()->user()->getStaffId();
        $now = Carbon::now()->toDateTimeString();
        $attributes = array_map(function ($item) use ($staffs, $staff_id, $now) {
            $array_name = explode(' ', $item['fullname']);

            return [
                'lastname' => trim(array_pop($array_name)),
                'firstname' => trim(implode(' ', $array_name)),
                'created_by' => auth()->getId(),
                'staff_id' => empty($staffs) || !isset($staffs[$item['staff_info']]) ? $staff_id : $staffs[$item['staff_info']],
                'status' => 1,
                'school_id' => $item['school_id'],
                'phone_number' => $item['phone_number'],
                'email' => $item['email'],
                'source' => $item['source'],
                'created_at' => $now,
                'staff_info' => $item['staff_info'],
            ];
        }, $data);

        return $this->contact_repository->insert($attributes);
    }
}
