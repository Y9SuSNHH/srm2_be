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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('learning_module_id');
            $table->unsignedBigInteger('student_id');
            $table->date('exam_date');
            $table->unsignedBigInteger('storage_file_id')->nullable();
            $table->string('note')->nullable();

            $table->unique(['learning_module_id', 'student_id', 'exam_date', 'deleted_time'], 'grades_uk');
            $table->foreign('learning_module_id')->references('id')->on('learning_modules');
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('storage_file_id')->references('id')->on('storage_files');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_grades BEFORE UPDATE ON grades FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_grades ON grades');
        Schema::dropIfExists('grades');
    }
};
