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
    public function up()
    {
        Schema::create('learning_modules', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('tag_code', 50);
            $table->unsignedTinyInteger('amount_credit');
            $table->date('apply_date')->default(\Illuminate\Support\Facades\DB::raw('current_timestamp'));
            $table->unsignedTinyInteger('grade_setting_div')->default(0);
            $table->string('alias', 252)->nullable();
            $table->unique(['tag_code', 'amount_credit', 'apply_date', 'deleted_time'], 'learning_modules_uk');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('subject_id')->references('id')->on('subjects');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_learning_modules BEFORE UPDATE ON learning_modules FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_learning_modules ON learning_modules');
        Schema::dropIfExists('learning_modules');
    }
};
