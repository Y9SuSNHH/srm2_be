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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('old_id')->nullable();
            $table->unsignedBigInteger('major_id');
            $table->unsignedBigInteger('enrollment_object_id');
            $table->unsignedBigInteger('area_id');
            $table->unsignedBigInteger('enrollment_wave_id');
            $table->unsignedTinyInteger('proposal')->default(0);
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('code', 50);
            $table->string('description', 255)->nullable();
            $table->unique(['school_id', 'code', 'deleted_time'], 'classrooms_code_uk');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('staff_id')->references('id')->on('staffs');
            $table->foreign('enrollment_object_id')->references('id')->on('enrollment_objects');
            $table->foreign('major_id')->references('id')->on('majors');
            $table->foreign('enrollment_wave_id')->references('id')->on('enrollment_waves');
            $table->foreign('area_id')->references('id')->on('areas');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_classrooms BEFORE UPDATE ON classrooms FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_classrooms ON classrooms');
        Schema::dropIfExists('classrooms');
    }
};
