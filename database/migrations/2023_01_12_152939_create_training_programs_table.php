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
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('major_id');
            $table->date('began_date');

            $table->unique(['school_id', 'major_id', 'began_date', 'deleted_time'], 'training_programs_uk');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('major_id')->references('id')->on('majors');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_training_programs BEFORE UPDATE ON training_programs FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_training_programs ON training_programs');
        Schema::dropIfExists('training_programs');
    }
};
