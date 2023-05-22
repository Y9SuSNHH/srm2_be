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
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('code', 50);
            $table->string('name', 255);
            $table->string('shortcode', 200);
            $table->unique(['school_id', 'area_id', 'code', 'deleted_time'], 'majors_uk');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('area_id')->references('id')->on('areas');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_majors BEFORE UPDATE ON majors FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_majors ON majors');
        Schema::dropIfExists('majors');
    }
};
