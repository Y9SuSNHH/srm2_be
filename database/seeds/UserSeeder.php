<?php

namespace Database\Seeders;

use App\Eloquent\Model;
use App\Eloquent\Permission;
use App\Eloquent\Role;
use App\Eloquent\Staff;
use App\Eloquent\User;
use App\Http\Enum\PermissionAction;
use App\Http\Enum\ReferenceType;

class UserSeeder extends \Illuminate\Database\Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var User $user */
        $user = User::query()->create([
            'username' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make('secret'),
        ]);

        /** @var Role $role */
        $role = Role::query()->create([
            'name' => \App\Http\Enum\Role::ADMIN,
            'authority' => \App\Http\Enum\RoleAuthority::SUPPER_ADMIN,
        ]);

        /** @var Staff $staff */
        $staff = Staff::query()->create([
            'fullname' => 'Administrator',
            'email' => 'admin@onschool.edu.vn',
            'team' => 'admin',
            'status' => 'working',
            'user_id' => $user->id ?? null,
        ]);

        $user->reference_type = ReferenceType::STAFF;
        $user->reference_id = $staff->id;
        $user->save();
        $user->roles()->attach($role->id);
        Role::query()->insert([
            ['name' => 'TKTS', 'authority' => \App\Http\Enum\RoleAuthority::ADMISSION_ADVISER],
            ['name' => 'Giáo Vụ', 'authority' => \App\Http\Enum\RoleAuthority::ACADEMIC_AFFAIRS_OFFICER],
            ['name' => 'QLHT', 'authority' => \App\Http\Enum\RoleAuthority::LEARNING_MANAGEMENT],
            ['name' => 'Kế Toán', 'authority' => \App\Http\Enum\RoleAuthority::ACCOUNTANT],
        ]);

        $create = array_map(function ($guard) {
            return ['guard' => $guard, 'action' => PermissionAction::CREATE];
        }, Model::getListEloquent());
        $edit = array_map(function ($guard) {
            return ['guard' => $guard, 'action' => PermissionAction::EDIT];
        }, Model::getListEloquent());
        $delete = array_map(function ($guard) {
            return ['guard' => $guard, 'action' => PermissionAction::DELETE];
        }, Model::getListEloquent());
        Permission::query()->insert(array_merge($create, $edit, $delete));
    }
}
