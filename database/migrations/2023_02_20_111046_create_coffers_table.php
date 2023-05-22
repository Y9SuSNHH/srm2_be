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
        Schema::create('coffers', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $table->unsignedTinyInteger('unit')->default(1);
            $table->unsignedTinyInteger('div');
            $table->string('identification', 50)->comment('CMND/CCCD');
            $table->string('firstname', 150);
            $table->string('lastname', 150);
            $table->string('birthday', 10);
            $table->longText('image')->default('')->comment('image base64');
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('credit')->default(0);
            $table->unique(['identification', 'unit'], 'coffers_uk');
        });

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE public.coffers ADD CONSTRAINT coffers_identification_check CHECK (((\"div\" || identification)::text ~ '^[1-8]\d{9}$|^[1-8]\d{12}$|^9\w+$'::text))");
        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_coffers BEFORE UPDATE ON coffers FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_coffers ON coffers');
        Schema::dropIfExists('coffers');
    }
};
