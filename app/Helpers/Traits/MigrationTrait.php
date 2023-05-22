<?php

namespace App\Helpers\Traits;

use Illuminate\Database\Schema\Blueprint;

trait MigrationTrait
{
    public function addTimestamps(Blueprint $table)
    {
        $table->timestamp('created_at')->nullable()->default(\Illuminate\Support\Facades\DB::raw('current_timestamp'));
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamp('updated_at')->nullable()->default(\Illuminate\Support\Facades\DB::raw('current_timestamp'));
        $table->unsignedBigInteger('updated_by')->nullable();
    }

    public function addSoftDelete(Blueprint $table)
    {
        $table->unsignedBigInteger('deleted_time')->default(0);
        $table->unsignedBigInteger('deleted_by')->nullable();
    }

    public function addCreatedAt(Blueprint $table)
    {
        $table->timestamp('created_at')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
    }

    public function addUpdatedAt(Blueprint $table)
    {
        $table->timestamp('updated_at')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
    }
}
