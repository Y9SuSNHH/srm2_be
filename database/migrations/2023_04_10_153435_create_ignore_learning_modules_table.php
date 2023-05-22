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
        Schema::create('ignore_learning_modules', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->nullable()->default(\Illuminate\Support\Facades\DB::raw('current_timestamp'));
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('learning_module_id');
            $table->string('reason', 255);
            $table->unsignedBigInteger('storage_file_id')->nullable();
            $table->unique(['student_id', 'learning_module_id'], 'ignore_learning_modules_uk');
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('learning_module_id')->references('id')->on('learning_modules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ignore_learning_modules');
    }
};
