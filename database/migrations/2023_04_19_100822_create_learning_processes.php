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
        Schema::create('learning_processes', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('learning_modules_id');
            $table->unsignedSmallInteger('result_btgk1', 50)->nullable();
            $table->unsignedSmallInteger('result_btgk2', 50)->nullable();
            $table->unsignedSmallInteger('result_diem_cc', 50)->nullable();
            $table->date('deadline_btgk1')->nullable();
            $table->date('deadline_btgk2')->nullable();
            $table->date('deadline_diem_cc')->nullable();
            $table->string('item_type', 16)->nullable();
            $table->foreign('student_id')->references('id')->on('student');
            $table->foreign('learning_modules_id')->references('id')->on('learning_modules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('learning_processes');
    }
};
