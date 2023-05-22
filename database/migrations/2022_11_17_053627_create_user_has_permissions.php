<?php

use App\Helpers\Traits\MigrationTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MigrationTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('user_id');
            $this->addTimestamps($table);

            $table->index('user_id');
            $table->primary(['permission_id', 'user_id']);
            $table->foreign('permission_id')->references('id')->on('permissions');
            $table->foreign('user_id')->references('id')->on('users');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_user_has_permissions BEFORE UPDATE ON user_has_permissions FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_user_has_permissions ON user_has_permissions');
        Schema::dropIfExists('user_has_permissions');
    }
};
