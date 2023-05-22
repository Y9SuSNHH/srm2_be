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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 255);
            $table->string('password', 255);
            $table->unsignedTinyInteger('reference_type')->default(0);
            $table->unsignedBigInteger('reference_id')->nullable();
            $this->addTimestamps($table);

            $table->unique('username');
            $table->unique(['reference_type', 'reference_id']);
        });

        \Illuminate\Support\Facades\DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION update_timestamp_updated_at() RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at := NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);
        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_users BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_users ON users');
        \Illuminate\Support\Facades\DB::statement('drop function if exists update_timestamp_updated_at');
        Schema::dropIfExists('users');
    }
};
