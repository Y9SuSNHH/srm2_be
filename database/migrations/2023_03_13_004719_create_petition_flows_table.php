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
        Schema::create('petition_flows', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at');
            $table->unsignedBigInteger('petition_id');
            $table->unsignedBigInteger('staff_id');
            $table->unsignedTinyInteger('status');
            $table->text('note')->nullable();
            $table->unsignedTinyInteger('role_authority');
            $table->boolean('is_update_student');

            $table->foreign('petition_id')->references('id')->on('petitions');
            $table->foreign('staff_id')->references('id')->on('staffs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('petition_flows');
    }
};
