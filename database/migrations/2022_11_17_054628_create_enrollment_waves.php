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
        Schema::create('enrollment_waves', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('area_id');
            $table->date('first_day_of_school');
            $table->unsignedSmallInteger('school_year')->nullable();
            $table->unsignedSmallInteger('group_number')->nullable();
            $table->date('enrollment_start_date')->nullable();
            $table->date('enrollment_end_date')->nullable();
            $table->date('application_submission_deadline')->nullable();
            $table->date('tuition_payment_deadline')->nullable();
            $table->unsignedTinyInteger('locked')->nullable();

            $table->unique(['school_id', 'area_id', 'first_day_of_school', 'deleted_time'], 'enrollment_waves_uk');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('area_id')->references('id')->on('areas');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_enrollment_waves BEFORE UPDATE ON enrollment_waves FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_enrollment_waves ON enrollment_waves');
        Schema::dropIfExists('enrollment_waves');
    }
};
