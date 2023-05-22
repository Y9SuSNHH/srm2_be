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
    public function up(): void
    {
        Schema::create('handovers', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedBigInteger('staff_id');
            $table->date('handover_date');
            $table->boolean('return_student_code_status')->default(false);
            $table->unsignedInteger('no')->nullable();
            $table->date('decision_date')->nullable();
            $table->date('first_day_of_school');
            $table->unsignedBigInteger('area_id');
            $table->unsignedTinyInteger('status');
            $table->unsignedTinyInteger('student_status')->nullable();
            $table->unsignedTinyInteger('profile_status')->nullable();
            $table->boolean('is_lock')->default(false);
            $this->addTimestamps($table);
            $this->addSoftDelete($table);

            $table->foreign('staff_id')->references('id')->on('staffs');
            $table->foreign('area_id')->references('id')->on('areas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('handovers');
    }
};
