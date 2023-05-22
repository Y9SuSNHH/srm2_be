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
        Schema::create('workflow_form_values', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedBigInteger('workflow_structure_form_id');
            $table->text('value');
            $table->unique(['workflow_id', 'workflow_structure_form_id', 'value'], 'workflow_form_values_uk');
            $table->foreign('workflow_id')->references('id')->on('workflows');
            $table->foreign('workflow_structure_form_id')->references('id')->on('workflow_structure_forms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_form_values');
    }
};
