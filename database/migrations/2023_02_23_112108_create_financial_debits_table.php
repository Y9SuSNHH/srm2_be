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
        Schema::create('financial_debits', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('student_profile_id');
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedTinyInteger('purpose');
            $table->string('receipt_no', 100);
            $table->date('receipt_date');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedTinyInteger('reference_type')->default(0);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedTinyInteger('payment_type')->nullable();
            $table->string('bank_name')->nullable();

            $table->unique(['receipt_no', 'purpose', 'deleted_time'], 'financial_debits_uk');
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
        Schema::dropIfExists('financial_debits');
    }
};
