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
        Schema::create('workflow_structure_forms', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('workflow_structure_id');
            $table->string('apply_table', 100);
            $table->string('field_name', 100);
            $table->unsignedBigInteger('field_div');
            $table->text('field_label')->default('');
            $table->string('validate_regex')->default('');
            $table->unsignedTinyInteger('order_field')->default(1);

            $table->unique(['workflow_structure_id', 'apply_table', 'field_name', 'deleted_time'], 'workflow_structure_forms_uk');
            $table->foreign('workflow_structure_id')->references('id')->on('workflow_structures');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_structure_forms');
    }
};
