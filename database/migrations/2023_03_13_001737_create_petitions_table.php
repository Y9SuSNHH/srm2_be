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
        Schema::create('petitions', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('student_id');
            $table->unsignedTinyInteger('content_type');
            $table->jsonb('current_content');
            $table->jsonb('new_content');
            $table->unsignedTinyInteger('status')->nullable();
            $table->date('effective_date');
            $table->date('date_of_amendment');
            $table->unsignedBigInteger('storage_file_id')->nullable();
            $table->unsignedInteger('no', 50)->nullable();

            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('storage_file_id')->references('id')->on('storage_files');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_petitions BEFORE UPDATE ON petitions FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_petitions ON petitions');
        Schema::dropIfExists('petitions');
    }
};
