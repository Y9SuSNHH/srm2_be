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
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('classroom_id');
            $table->unsignedTinyInteger('semester');
            $table->date('decision_date')->nullable();
            $table->date('collect_began_date');
            $table->date('collect_ended_date');
            $table->date('learn_began_date');
            $table->date('expired_date_com');
            $table->date('learn_ended_date');
            $table->boolean('is_final')->default(false);
            $table->string('wait', 255)->nullable();
            $table->unique(['classroom_id', 'semester'], 'periods_uk');
            $table->foreign('classroom_id')->references('id')->on('classrooms');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_periods BEFORE UPDATE ON periods FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_periods ON periods');
        Schema::dropIfExists('periods');
    }
};
