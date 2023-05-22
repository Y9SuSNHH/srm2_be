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
        Schema::create('workflow_approval_steps', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('workflow_structure_id');
            $table->unsignedTinyInteger('approval_step')->default(1);
            $table->unsignedBigInteger('approval_group_user_id');
            $table->boolean('is_fully')->default(false);

            $table->unique(['workflow_structure_id', 'approval_step', 'deleted_time'], 'workflow_approval_steps_uk');
            $table->foreign('workflow_structure_id')->references('id')->on('workflow_structures');
            $table->foreign('approval_group_user_id')->references('id')->on('approval_group_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_approval_steps');
    }
};
