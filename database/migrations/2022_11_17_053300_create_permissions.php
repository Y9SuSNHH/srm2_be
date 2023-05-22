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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('guard', 250);
            $table->unsignedBigInteger('action')->default(\App\Http\Enum\PermissionAction::READ);
            $table->text('constraint')->nullable();
            $this->addTimestamps($table);

            $table->unique(['guard', 'action'], 'permissions_un');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_permissions BEFORE UPDATE ON permissions FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_permissions ON permissions');
        Schema::dropIfExists('permissions');
    }
};
