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
        Schema::create('sv50_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('staff_id');
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->string('phone_number', 20);
            $table->string('email', 255)->nullable();
            $table->string('source', 255)->nullable();
            $table->string('link', 255)->nullable();
            $table->tinyInteger('status');
            $this->addTimestamps($table);
            $this->addSoftDelete($table);

            $table->foreign('school_id')->references('id')->on('schools');
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
        Schema::dropIfExists('sv50_contacts');
    }
};
