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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('workflow_structure_id');
            $table->unsignedTinyInteger('approval_status');
            $table->boolean('is_close')->default(false);
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('backlog_id')->nullable();

            $table->foreign('workflow_structure_id')->references('id')->on('workflow_structures');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('backlog_id')->references('id')->on('backlogs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflows');
    }
};
