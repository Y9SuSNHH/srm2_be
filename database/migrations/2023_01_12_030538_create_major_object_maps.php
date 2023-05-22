<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use \App\Helpers\Traits\MigrationTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('major_object_maps', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $table->unsignedBigInteger('major_id');
            $table->unsignedBigInteger('enrollment_object_id');

            $table->foreign('major_id')->references('id')->on('majors');
            $table->foreign('enrollment_object_id')->references('id')->on('enrollment_objects');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_major_object_maps BEFORE UPDATE ON major_object_maps FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_major_object_maps ON major_object_maps');
        Schema::dropIfExists('major_object_maps');
    }
};
