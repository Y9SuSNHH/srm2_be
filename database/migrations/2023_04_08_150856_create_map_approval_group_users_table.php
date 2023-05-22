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
        Schema::create('map_approval_group_users', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->nullable()->default(\Illuminate\Support\Facades\DB::raw('current_timestamp'));
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approval_group_user_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_decision')->default(false);
            $table->unique(['approval_group_user_id', 'user_id'], 'map_approval_group_users_uk');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('map_approval_group_users');
    }
};
