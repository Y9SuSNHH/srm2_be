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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $table->string('school_code', 50);
            $table->string('school_name', 150);
            $table->string('theme', 50)->default('');
            $table->unsignedSmallInteger('school_status')->default(1);
            $table->text('service_name')->default('');
            $table->unsignedBigInteger('priority')->default(1);

            $table->unique(['school_code'], 'schools_code_uk');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_schools BEFORE UPDATE ON schools FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_schools ON schools');
        Schema::dropIfExists('schools');
    }
};
