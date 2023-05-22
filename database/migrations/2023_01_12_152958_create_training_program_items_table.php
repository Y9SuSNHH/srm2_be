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
        Schema::create('training_program_items', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('training_program_id');
            $table->unsignedBigInteger('learning_module_id');
            $table->unsignedBigInteger('enrollment_object_id');
            $table->unsignedBigInteger('subject_id');

            $table->unique(['training_program_id', 'enrollment_object_id', 'subject_id', 'deleted_time'], 'training_program_items_uk');
            $table->foreign('training_program_id')->references('id')->on('training_programs');
            $table->foreign('learning_module_id')->references('id')->on('learning_modules');
            $table->foreign('enrollment_object_id')->references('id')->on('enrollment_objects');
            $table->foreign('subject_id')->references('id')->on('subjects');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_training_program_items BEFORE UPDATE ON training_program_items FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_training_program_items ON training_program_items');
        Schema::dropIfExists('training_program_items');
    }
};
