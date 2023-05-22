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
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->string('fullname', 255);
            $table->string('email', 255);
            $table->string('team', 255)->nullable();
            $table->date('day_off')->nullable();
            $table->string('status', 255)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->unique(['email', 'deleted_time'], 'staffs_uk');
            $table->unique(['user_id', 'deleted_time'], 'staffs_user_id_uk');
            $table->foreign('user_id')->references('id')->on('users');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_staffs BEFORE UPDATE ON staffs FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_staffs ON staffs');
        Schema::dropIfExists('staffs');
    }
};
