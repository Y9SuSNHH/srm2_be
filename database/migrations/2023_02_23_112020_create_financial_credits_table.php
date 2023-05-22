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
        Schema::create('financial_credits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $this->addSoftDelete($table);
            $this->addTimestamps($table);
            $table->unsignedBigInteger('student_profile_id');
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedTinyInteger('purpose');
            $table->string('no', 100)->default('');
            $table->unsignedBigInteger('transaction_id');
            $table->text('note')->nullable();

            $table->unique(['student_profile_id', 'purpose', 'no', 'deleted_time'], 'financial_credits_uk');
            $table->foreign('student_profile_id')->references('id')->on('student_profiles');
            $table->foreign('transaction_id')->references('id')->on('transactions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_credits');
    }
};
