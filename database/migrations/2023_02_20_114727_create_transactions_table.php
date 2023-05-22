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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $this->addSoftDelete($table);
            $this->addTimestamps($table);
            $table->string('code');
            $table->unsignedTinyInteger('unit')->default(1);
            $table->unsignedBigInteger('student_profile_id');
            $table->boolean('is_debt')->default(false);
            $table->unsignedBigInteger('financial_id');
            $table->unsignedBigInteger('amount')->default(0);
            $table->text('note')->nullable();

            $table->unique(['code', 'is_debt', 'deleted_time'], 'transactions_uk');
            $table->foreign('student_profile_id')->references('id')->on('student_profiles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
