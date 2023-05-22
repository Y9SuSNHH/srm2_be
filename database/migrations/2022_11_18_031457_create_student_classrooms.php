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
        Schema::create('student_classrooms', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('classroom_id');
            $table->date('began_date');
            $table->timestamp('began_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedTinyInteger('reference_type')->default(0);
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->unique(['student_id', 'began_at'], 'student_classrooms_uk');
            $table->unique(['reference_type', 'reference_id'], 'student_classrooms_uk_2');
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('classroom_id')->references('id')->on('classrooms');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_student_classrooms BEFORE UPDATE ON student_classrooms FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');

        \Illuminate\Support\Facades\DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION validate_insert_student_classrooms() RETURNS TRIGGER AS $$
BEGIN
    IF (EXISTS (SELECT 1 FROM student_classrooms WHERE student_id::int = NEW.student_id::int AND began_at::timestamp > NEW.began_at::timestamp)) THEN
        RAISE EXCEPTION '#EC3: invalid student_classrooms.began_at!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_insert_student_classrooms BEFORE INSERT ON student_classrooms FOR EACH ROW EXECUTE FUNCTION validate_insert_student_classrooms()');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_insert_student_classrooms ON student_classrooms');
        \Illuminate\Support\Facades\DB::statement('DROP function if exists validate_insert_student_classrooms');
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_student_classrooms ON student_classrooms');
        Schema::dropIfExists('student_classrooms');
    }
};
