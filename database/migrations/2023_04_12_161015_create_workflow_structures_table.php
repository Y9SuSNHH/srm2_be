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
        Schema::create('workflow_structures', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('apply_div');
            $table->boolean('is_custom_form')->default(false);
            $table->string('model')->nullable();
            $table->string('alias')->nullable();

            $table->unique(['school_id', 'alias'], 'workflow_structures_uk');
            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_structures');
    }
};
