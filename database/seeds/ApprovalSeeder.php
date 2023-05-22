<?php

namespace Database\Seeders;

use App\Eloquent\ApprovalGroupUser;
use App\Eloquent\WorkflowApprovalStep;
use App\Eloquent\WorkflowStructure;
use App\Eloquent\School;
use App\Http\Domain\Student\Form\StudentForm;
use App\Http\Enum\WorkflowApplyDiv;

class ApprovalSeeder extends \Illuminate\Database\Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $workflow_structure = WorkflowStructure::query()->create([
            'school_id' => optional(School::query()->where('school_code', 'TVU')->first())->id ?? 0,
            'name' => 'Approve to edit student information at TVU',
            'apply_div' => WorkflowApplyDiv::EDIT_STUDENT,
            'model' => StudentForm::class,
            'alias' => WorkflowApplyDiv::EDIT_STUDENT()->getKey(),
        ]);
        $approval_group_user = ApprovalGroupUser::query()->create([
            'school_id' => optional(School::query()->where('school_code', 'TVU')->first())->id ?? 0,
            'name' => 'Nhóm phê duyệt chỉnh sửa sinh viên',
        ]);
        WorkflowApprovalStep::query()->create([
            'workflow_structure_id' => $workflow_structure->id,
            'approval_step' => 1,
            'approval_group_user_id' => $approval_group_user->id,
            'is_fully' => false,
        ]);
    }
}