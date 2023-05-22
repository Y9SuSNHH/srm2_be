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
        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedBigInteger('workflow_approval_step_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('approval_status');
            $table->timestamp('approval_at')->nullable();
            $table->boolean('is_final')->default(false);
            $table->text('comment')->nullable();

            $table->unique(['workflow_id', 'workflow_approval_step_id', 'user_id'], 'workflow_approvals_uk');
            $table->foreign('workflow_id')->references('id')->on('workflows');
            $table->foreign('workflow_approval_step_id')->references('id')->on('workflow_approval_steps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_approvals');
    }
};
