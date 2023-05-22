<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Models;

use App\Eloquent\Handover as EloquentHandover;
use App\Helpers\Json;
use App\Http\Enum\HandoverStatus;
use App\Http\Enum\ProfileStatus;
use App\Http\Enum\StudentStatus;
use ReflectionException;

class Handover extends Json
{
    public $id;
    public $code;
    public $staff_id;
    public $staff;
    public $handover_date;
    public $return_student_code_status;
    public $return_student_code_status_name;
    public $no;
    public $decision_date;
    public $first_day_of_school;
    public $area_id;
    public $area;
    public $is_lock;
    public $is_lock_name;
    public $status;
    public $status_name;
    public $student_status;
    public $student_status_name;
    public $profile_status;
    public $profile_status_name;
    public $student_profiles_count;
    public $student_profiles;

    /**
     * @param EloquentHandover $handover
     * @throws ReflectionException
     */
    public function __construct(EloquentHandover $handover)
    {
        $student_status_name = null;
        $profile_status_name = null;
        $status_name         = null;
        if (StudentStatus::isValid($handover->student_status)) {
            $student_status_name = StudentStatus::from($handover->student_status)->getLang();
        }
        if (ProfileStatus::isValid($handover->profile_status)) {
            $profile_status_name = ProfileStatus::from($handover->profile_status)->getKey();
        }
        if (HandoverStatus::isValid($handover->status)) {
            $status_name = HandoverStatus::from($handover->status)->getLang();
        }

        $return_student_code_status_name = $handover->return_student_code_status ? 'Đã trả' : 'Chưa trả';

        $is_lock_name = $handover->is_lock ? 'Đã trả' : 'Chưa trả';

        parent::__construct(array_merge($handover->toArray(), [
            'student_status_name'             => $student_status_name,
            'profile_status_name'             => $profile_status_name,
            'status_name'                     => $status_name,
            'return_student_code_status_name' => $return_student_code_status_name,
            'is_lock_name'                => $is_lock_name,
        ]));
    }

    public static function dates(): array
    {
        return [
            'handover_date',
            'decision_date',
            'first_day_of_school',
        ];
    }
}