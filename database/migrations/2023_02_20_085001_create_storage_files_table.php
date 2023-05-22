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
        Schema::create('storage_files', function (Blueprint $table) {
            $table->id();
            $this->addTimestamps($table);
            $this->addSoftDelete($table);
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('storage_div')->default(1)->comment('bitwise: 1: local');
            $table->string('file_path');
            $table->string('file_type', 100);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('origin_name');
            $table->unsignedBigInteger('file_div');
            $table->unsignedBigInteger('uploader');
            $table->string('file_url')->nullable();

            $table->unique(['file_path'], 'storage_files_uk');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('uploader')->references('id')->on('users');
        });

        \Illuminate\Support\Facades\DB::statement('CREATE TRIGGER trigger_storage_files BEFORE UPDATE ON storage_files FOR EACH ROW EXECUTE FUNCTION update_timestamp_updated_at()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('DROP TRIGGER IF EXISTS trigger_storage_files ON storage_files');
        Schema::dropIfExists('storage_files');
    }
};
