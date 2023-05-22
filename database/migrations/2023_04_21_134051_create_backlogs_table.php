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
        Schema::create('backlogs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->nullable()->default(\Illuminate\Support\Facades\DB::raw('current_timestamp'));
            $table->timestamp('updated_at')->nullable()->default(\Illuminate\Support\Facades\DB::raw('current_timestamp'));
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedTinyInteger('work_div');
            $table->unsignedTinyInteger('work_status')->default(\App\Http\Enum\WorkStatus::WAIT);
            $table->jsonb('work_payload')->default('[]');
            $table->jsonb('reference')->default('[]');
            $table->longText('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backlogs');
    }
};
