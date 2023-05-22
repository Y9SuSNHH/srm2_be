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
        Schema::create('study_plans', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('classroom_id');
            $table->unsignedTinyInteger('semester');
            $table->unsignedTinyInteger('slot');
            $table->unsignedBigInteger('learning_module_id');
            $table->unsignedBigInteger('subject_id');
            $table->date('study_began_date');
            $table->date('study_ended_date');
            $table->date('day_of_the_test');

            $table->unique(['classroom_id', 'subject_id', 'deleted_time'], 'training_program_items_uk1');
            $table->unique(['classroom_id', 'semester', 'slot', 'deleted_time'], 'training_program_items_uk2');
            $table->foreign('classroom_id')->references('id')->on('classrooms');
            $table->foreign('learning_module_id')->references('id')->on('learning_modules');
            $table->foreign('subject_id')->references('id')->on('subjects');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_study_plans BEFORE UPDATE ON study_plans FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_study_plans ON study_plans');
        Schema::dropIfExists('study_plans');
    }
};
