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
        Schema::create('student_revision_histories', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $table->unsignedBigInteger('student_id');
            $table->unsignedSmallInteger('type');
            $table->string('value', 255);
            $table->date('began_date');
            $table->timestamp('began_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedTinyInteger('reference_type')->default(0);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unique(['student_id', 'type', 'began_at'], 'student_revision_histories_uk');
            $table->unique(['reference_type', 'reference_id'], 'student_revision_histories_uk_2');
            $table->foreign('student_id')->references('id')->on('students');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_student_revision_histories BEFORE UPDATE ON student_revision_histories FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');

        \Illuminate\Support\Facades\DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION validate_insert_student_revision_histories() RETURNS TRIGGER AS $$
BEGIN
    IF (EXISTS (SELECT 1 FROM student_revision_histories WHERE student_id::int = NEW.student_id::int AND "type"::int = NEW."type"::int AND began_at::timestamp > NEW.began_at::timestamp)) THEN
        RAISE EXCEPTION '#EC2: invalid student_revision_histories.began_at!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_insert_student_revision_histories BEFORE INSERT ON student_revision_histories FOR EACH ROW EXECUTE FUNCTION validate_insert_student_revision_histories()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_insert_student_revision_histories ON student_revision_histories');
        \Illuminate\Support\Facades\DB::statement('DROP function if exists validate_insert_student_revision_histories');
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_student_revision_histories ON student_revision_histories');
        Schema::dropIfExists('student_revision_histories');
    }
};
