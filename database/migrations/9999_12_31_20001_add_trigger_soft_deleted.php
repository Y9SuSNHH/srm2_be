<?php

use Illuminate\Database\Migrations\Migration;;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE TRIGGER trigger_request_structures_soft_deleted BEFORE UPDATE ON request_structures FOR EACH ROW EXECUTE FUNCTION check_soft_deleted_request_structures()');
        DB::statement('CREATE TRIGGER trigger_requests_soft_deleted BEFORE UPDATE ON requests FOR EACH ROW EXECUTE FUNCTION check_soft_deleted_requests()');
        DB::statement('CREATE TRIGGER trigger_approval_group_users_soft_deleted BEFORE UPDATE ON approval_group_users FOR EACH ROW EXECUTE FUNCTION check_soft_deleted_approval_group_users()');
        DB::statement('CREATE TRIGGER trigger_request_approval_steps_soft_deleted BEFORE UPDATE ON request_approval_steps FOR EACH ROW EXECUTE FUNCTION check_soft_deleted_request_approval_steps()');
        DB::statement('CREATE TRIGGER trigger_request_structure_forms_soft_deleted BEFORE UPDATE ON request_structure_forms FOR EACH ROW EXECUTE FUNCTION check_soft_deleted_request_structure_forms()');


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP TRIGGER IF EXISTS trigger_request_structures_soft_deleted ON request_structures');
        DB::statement('DROP TRIGGER IF EXISTS trigger_requests_soft_deleted ON requests');
        DB::statement('DROP TRIGGER IF EXISTS trigger_approval_group_users_soft_deleted ON approval_group_users');
        DB::statement('DROP TRIGGER IF EXISTS trigger_request_approval_steps_soft_deleted ON request_approval_steps');
        DB::statement('DROP TRIGGER IF EXISTS trigger_request_structure_forms_soft_deleted ON request_structure_forms');

    }
};
