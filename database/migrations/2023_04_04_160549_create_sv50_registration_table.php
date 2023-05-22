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
        Schema::create('sv50_registration', function (Blueprint $table) {
            $table->id();
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->unsignedTinyInteger('gender');
            $table->string('identification', 20);
            $table->jsonb('identification_info')->nullable();
            $table->jsonb('residence')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedSmallInteger('year_of_birth')->nullable();
            $table->string('place_of_birth', 255)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('ethnic', 100)->nullable();
            $table->string('religion', 100)->nullable();
            $table->string('national', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->jsonb('address')->nullable();
            $table->unsignedTinyInteger('area_id');
            $table->unsignedBigInteger('staff_id');
            $table->unsignedTinyInteger('major_id');
            $table->jsonb('graduate')->nullable();
            $table->jsonb('curriculum_vitae')->nullable();

            $this->addTimestamps($table);
            $this->addSoftDelete($table);

            $table->foreign('area_id')->references('id')->on('areas');
            $table->foreign('staff_id')->references('id')->on('staffs');
            $table->foreign('major_id')->references('id')->on('majors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sv50_registration');
    }
};
