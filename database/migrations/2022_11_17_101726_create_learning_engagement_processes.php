<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learning_engagement_processes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->timestamp('last_modified')->default(\Illuminate\Support\Facades\DB::raw('now()'));
            $table->unsignedBigInteger('modified_by');
            $table->unsignedTinyInteger('is_join_first_day_of_school')->default(0);
            $table->unsignedTinyInteger('is_join_first_week')->default(0);
            $table->unsignedTinyInteger('is_join_fourth_week')->default(0);
            $table->string('student_type_first_week', 20)->nullable();
            $table->string('student_type_fourth_week', 20)->nullable();

            $table->unique('student_id', 'learning_engagement_processes_uk');
            $table->foreign('student_id')->references('id')->on('students');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('learning_engagement_processes');
    }
};
