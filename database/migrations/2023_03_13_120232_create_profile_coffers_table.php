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
        Schema::create('profile_coffers', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $table->unsignedBigInteger('profile_id');
            $table->unsignedBigInteger('coffer_id');
            $table->unique(['profile_id', 'coffer_id'], 'profile_coffers_uk');
            $table->foreign('profile_id')->references('id')->on('profiles');
            $table->foreign('coffer_id')->references('id')->on('coffers');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_profile_coffers BEFORE UPDATE ON profile_coffers FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_profile_coffers ON profile_coffers');
        Schema::dropIfExists('profile_coffers');
    }
};
