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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_profile_id');
            $table->string('student_code', 50)->nullable();
            $table->string('account', 255)->comment('Tài khoản học tập');
            $table->string('email', 255)->comment('Email học tập');
            $table->string('profile_status', 255)->nullable();
            $table->unsignedTinyInteger('student_status')->nullable();
            $table->text('note')->nullable();

            $table->unique(['school_id', 'student_profile_id', 'deleted_time'], 'students_student_profile_id_uk');
            $table->unique(['school_id', 'student_code', 'deleted_time'], 'students_code_uk');
            $table->unique(['school_id', 'account', 'deleted_time'], 'students_account_uk');

            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('student_profile_id')->references('id')->on('student_profiles');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_students BEFORE UPDATE ON students FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_students ON students');
        Schema::dropIfExists('students');
    }
};
