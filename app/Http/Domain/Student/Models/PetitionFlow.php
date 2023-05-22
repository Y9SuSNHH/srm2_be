<?php

namespace App\Http\Domain\Student\Models;

use App\Eloquent\PetitionFlow as EloquentPetitionFlow;
use App\Helpers\Json;
use App\Http\Enum\PetitionFlowStatus;
use App\Http\Enum\PetitionStatus;
use App\Http\Enum\RoleAuthority;
use ReflectionException;

/**
 *
 */
class PetitionFlow extends Json
{
    public $id;
    public $petition_id;
    public $staff_id;
    public $staff;
    public $status;
    public $status_name;
    public $created_at;
    public $note;
    public $role_authority;
    public $is_update_student;


    /**
     * @param EloquentPetitionFlow $petition_flow
     * @throws ReflectionException
     */
    public function __construct(EloquentPetitionFlow $petition_flow)
    {
//        dd(RoleAuthority::LEARNING_MANAGEMENT()->validate($petition_flow->role_authority));
        $role_authority_name = '';
        $role_authority      = RoleAuthority::LEARNING_MANAGEMENT;
        if (RoleAuthority::LEARNING_MANAGEMENT()->validate($petition_flow->role_authority)) {
            $role_authority_name = RoleAuthority::LEARNING_MANAGEMENT()->getLang();
        }
        if (RoleAuthority::ACADEMIC_AFFAIRS_OFFICER()->validate($petition_flow->role_authority)) {
            $role_authority      = RoleAuthority::ACADEMIC_AFFAIRS_OFFICER;
            $role_authority_name = RoleAuthority::ACADEMIC_AFFAIRS_OFFICER()->getLang();
        }
        if (in_array($petition_flow->status, PetitionStatus::thirdParty())) {
            $status_name = PetitionFlowStatus::from($petition_flow->status)->getLang();
        } else {
            $status_name = $role_authority_name . ' ' . PetitionFlowStatus::from($petition_flow->status)->getLang();
        }

        if ((int)$petition_flow->status === PetitionFlowStatus::THIRD_PARTY_ACCEPT && (int)$petition_flow->role_authority === RoleAuthority::ACADEMIC_AFFAIRS_OFFICER) {
            $status_name = PetitionStatus::from(PetitionFlowStatus::THIRD_PARTY_ACCEPT)->getLang();
        }
        parent::__construct(array_merge($petition_flow->toArray(), [
            'status_name'    => $status_name,
            'role_authority' => $role_authority,
        ]));
    }

    public static function dates(): array
    {
        return [
            'created_at',
        ];
    }
}