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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('profile_id');
            $table->string('profile_code', 50);
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('credit')->default(0);
            $table->boolean('is_ts8')->default(false);
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedBigInteger('major_id')->nullable();
            $table->unsignedBigInteger('enrollment_object_id')->nullable();
            $table->unsignedBigInteger('enrollment_wave_id')->nullable();
            $table->unsignedBigInteger('classroom_id')->nullable();
            $table->jsonb('documents')->nullable();
            $table->unsignedBigInteger('handover_id')->nullable();

            $table->unique(['school_id', 'profile_code', 'deleted_time'], 'student_profiles_profile_code_uk');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('profile_id')->references('id')->on('profiles');
            $table->foreign('area_id')->references('id')->on('areas');
            $table->foreign('major_id')->references('id')->on('majors');
            $table->foreign('enrollment_object_id')->references('id')->on('enrollment_objects');
            $table->foreign('enrollment_wave_id')->references('id')->on('enrollment_waves');
            $table->foreign('classroom_id')->references('id')->on('classrooms');
            $table->foreign('handover_id')->references('id')->on('handovers');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_student_profiles BEFORE UPDATE ON student_profiles FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_student_profiles ON student_profiles');
        Schema::dropIfExists('student_profiles');
    }
};
