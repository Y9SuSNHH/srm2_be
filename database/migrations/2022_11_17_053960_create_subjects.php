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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->string('code', 50);
            $table->string('name', 255);
            $table->text('description');
            $table->unique(['code', 'school_id', 'deleted_time'], 'subjects_uk');
            $table->foreign('school_id')->references('id')->on('schools');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_subjects BEFORE UPDATE ON subjects FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_subjects ON subjects');
        Schema::dropIfExists('subjects');
    }
};
