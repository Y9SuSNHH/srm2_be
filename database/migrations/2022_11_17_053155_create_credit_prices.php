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
        Schema::create('credit_prices', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->date('effective_date');
            $table->unsignedBigInteger('price');
            $table->boolean('lock')->default(false);
            $table->unique(['effective_date', 'school_id', 'deleted_time'], 'credit_prices_uk');
            $table->foreign('school_id')->references('id')->on('schools');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_credit_prices BEFORE UPDATE ON credit_prices FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_credit_prices ON credit_prices');
        Schema::dropIfExists('credit_prices');
    }
};
