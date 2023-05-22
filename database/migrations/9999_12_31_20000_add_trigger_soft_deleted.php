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
        DB::statement(<<<SQL
CREATE TRIGGER trigger_areas_soft_deleted
BEFORE UPDATE ON areas
FOR EACH ROW
EXECUTE FUNCTION check_soft_deleted_areas()
SQL);

        DB::statement(<<<SQL
CREATE TRIGGER trigger_enrollment_objects_soft_deleted
BEFORE UPDATE ON enrollment_objects
FOR EACH ROW
EXECUTE FUNCTION check_soft_deleted_enrollment_objects()
SQL);

        DB::statement(<<<SQL
CREATE TRIGGER trigger_subjects_soft_deleted
BEFORE UPDATE ON subjects
FOR EACH ROW
EXECUTE FUNCTION check_soft_deleted_subjects()
SQL);

        DB::statement(<<<SQL
CREATE TRIGGER trigger_profiles_soft_deleted
BEFORE UPDATE ON profiles
FOR EACH ROW
EXECUTE FUNCTION check_soft_deleted_profiles()
SQL);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP TRIGGER IF EXISTS trigger_areas_soft_deleted ON areas');
        DB::statement('DROP TRIGGER IF EXISTS trigger_enrollment_objects_soft_deleted ON enrollment_objects');
        DB::statement('DROP TRIGGER IF EXISTS trigger_subjects_soft_deleted ON subjects');
        DB::statement('DROP TRIGGER IF EXISTS trigger_profiles_soft_deleted ON profiles');

    }
};
