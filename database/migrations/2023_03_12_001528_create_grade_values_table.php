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
        Schema::create('grade_values', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $table->unsignedTinyInteger('grade_div');
            $table->unsignedBigInteger('grade_id');
            $table->float('value');
            $table->unique(['grade_div', 'grade_id'], 'grade_values_uk');

            $table->foreign('grade_id')->references('id')->on('grades');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_grade_values BEFORE UPDATE ON grade_values FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_grade_values ON grade_values');
        Schema::dropIfExists('grade_values');
    }
};
