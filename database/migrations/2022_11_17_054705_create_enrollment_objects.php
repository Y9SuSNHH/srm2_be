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
        Schema::create('enrollment_objects', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->string('code', 50);
            $table->string('classification', 1)->default('');
            $table->string('name', 255);
            $table->string('shortcode', 200);
            $table->unique(['school_id', 'code', 'classification', 'deleted_time'], 'enrollment_objects_uk');
            $table->unique(['school_id', 'shortcode', 'deleted_time'], 'enrollment_objects_uk_2');
            $table->foreign('school_id')->references('id')->on('schools');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_enrollment_objects BEFORE UPDATE ON enrollment_objects FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_enrollment_objects ON enrollment_objects');
        Schema::dropIfExists('enrollment_objects');
    }
};
