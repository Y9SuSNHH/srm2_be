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
        Schema::create('study_sessions', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedTinyInteger('semester');
            $table->unsignedBigInteger('classroom_id');
            $table->unsignedBigInteger('began_study_plan_id');
            $table->unsignedBigInteger('ended_study_plan_id');
            $table->date('decision_date')->nullable();
            $table->date('collect_began_date');
            $table->date('collect_ended_date');
            $table->date('study_began_date');
            $table->date('expired_date_com');
            $table->date('study_ended_date');
            $table->boolean('is_final')->default(false);

            $table->unique(['classroom_id', 'semester', 'deleted_time'], 'study_sessions_uk1');
            $table->unique(['classroom_id', 'began_study_plan_id', 'deleted_time'], 'study_sessions_uk2');
            $table->unique(['classroom_id', 'ended_study_plan_id', 'deleted_time'], 'study_sessions_uk3');
            $table->foreign('classroom_id')->references('id')->on('classrooms');
            $table->foreign('began_study_plan_id')->references('id')->on('study_plans');
            $table->foreign('ended_study_plan_id')->references('id')->on('study_plans');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_study_sessions BEFORE UPDATE ON study_sessions FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_study_sessions ON study_sessions');
        Schema::dropIfExists('study_sessions');
    }
};
